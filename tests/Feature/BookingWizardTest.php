<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\DriverProfile;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BookingWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_booking_collects_the_deposit_before_matching_a_driver(): void
    {
        config([
            'booking.osrm_base_url' => 'https://routes.example.test',
            'booking.deposit_percent' => '20',
            'booking.commission_percent' => '10',
            'booking.deposit_minutes' => '30',
            'payments.clik_name' => 'Test Beneficiary',
            'payments.clik_id' => 'TEST',
        ]);
        Http::fake(['routes.example.test/*' => Http::response(['routes' => [['distance' => 2500]]])]);
        $customer = User::factory()->create();
        $type = VehicleType::factory()->create(['name' => 'Small van', 'is_active' => true]);
        $driver = DriverProfile::factory()->create();
        $driver->user->update(['name' => 'Assigned Driver', 'phone' => '0791234567']);
        $vehicle = Vehicle::factory()->for($type)->for($driver, 'driver')->create([
            'name' => 'Assigned Van', 'plate_number' => 'TEST-418', 'status' => 'active', 'base_price' => '12.000', 'price_per_km' => '2.000',
        ]);
        Vehicle::factory()->for($type)->create(['status' => 'active', 'base_price' => '19.000', 'price_per_km' => '2.000']);
        Service::factory()->create(['type' => Service::TYPE_TRANSPORT, 'unit' => 'fixed', 'price' => '3.000', 'is_active' => true]);

        $this->actingAs($customer);
        $this->get(route('booking.create'))->assertOk()
            ->assertSee('Choose a vehicle size')
            ->assertSee('Add furniture manually')
            ->assertSee('Scan with your phone')
            ->assertSee('type="radio" name="booking_method"', false)
            ->assertSee('Start booking');
        $response = $this->post(route('booking.store'), ['booking_method' => 'quick']);
        $booking = $customer->bookings()->sole();
        $step = fn ($name) => route('booking.edit', ['booking' => $booking, 'step' => $name]);
        $response->assertRedirect($step('type'));
        $this->get($step('vehicles'))->assertOk()
            ->assertSee('Choose your vehicle size')
            ->assertSee('landing/img/vehicle-compact-van.svg')
            ->assertSee('type="radio" name="preferred_vehicle_type_id"', false);
        $this->put(route('booking.update', $booking), ['booking_step' => 'type', 'preferred_vehicle_type_id' => $type->id])
            ->assertRedirect($step('route'));
        $this->assertNull($booking->refresh()->vehicle_id);
        $this->get($step('route'))->assertOk()->assertSee('Pickup and destination')->assertDontSee('name="scheduled_start_at"', false);
        $this->put(route('booking.update', $booking), [
            'booking_step' => 'route', 'pickup_latitude' => '31.91', 'pickup_longitude' => '35.9',
            'destination_latitude' => '31.98', 'destination_longitude' => '35.92',
        ])->assertRedirect($step('time'));
        $this->get($step('time'))->assertOk()->assertSee('Date and time (Amman)')->assertDontSee('name="scheduled_end_at"', false);
        $this->put(route('booking.update', $booking), [
            'booking_step' => 'time', 'scheduled_start_at' => now('Asia/Amman')->addDay()->format('Y-m-d\TH:i'),
        ])->assertRedirect(route('booking.show', $booking).'#services');
        $this->get($step('vehicles'))->assertRedirect(route('booking.show', $booking).'#services');
        $this->getJson(route('bookings.vehicles', ['start' => $booking->refresh()->scheduled_start_at->toISOString(), 'booking_id' => $booking->id]))
            ->assertExactJson(['available' => true]);
        $this->putJson(route('booking.update', $booking), ['booking_step' => 'time', 'vehicle_id' => $vehicle->id])
            ->assertUnprocessable()->assertJsonValidationErrors('vehicle_id');
        $this->get(route('booking.show', $booking))->assertOk()
            ->assertSee('Review and send')->assertDontSee('Furniture inventory')->assertDontSee('Assigned Van');
        $this->assertSame(0, $booking->inventoryItems()->count());
        $this->postJson(route('bookings.quote', $booking))->assertOk()
            ->assertJsonPath('total_price', '20.000')->assertJsonMissingPath('vehicle_id')->assertJsonMissingPath('driver_id');
        $this->assertNull($booking->refresh()->vehicle_id);
        $paymentUrl = route('payments.create', ['booking_id' => $booking->id]);
        $this->postJson(route('bookings.submit', $booking), ['expected_total' => '20.000'])
            ->assertOk()->assertJsonPath('status', 'awaiting_deposit')->assertJsonPath('payment_url', $paymentUrl);
        $this->assertNull($booking->refresh()->vehicle_id);
        $this->assertNull($booking->driver_id);
        $this->assertTrue($booking->deposit_due_at->isFuture());
        $details = route('booking.show', $booking);
        $this->getJson($details)->assertOk()->assertJsonMissingPath('driver')->assertJsonMissingPath('vehicle');
        $this->get($details)->assertOk()->assertSee('Pay deposit via CliQ')->assertSee($paymentUrl);
        $this->get(route('booking.index'))->assertOk()->assertDontSee('Assigned Van');
        $this->actingAs($driver->user)->patchJson(route('driver.bookings.accept', $booking))->assertNotFound();

        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        foreach (['1.000', '3.000'] as $index => $amount) {
            $this->actingAs($customer)->post(route('payments.store'), [
                'booking_id' => $booking->id, 'amount' => $amount,
                'transfer_reference' => 'ASSIGN-'.($index + 1),
                'receipt_image' => new UploadedFile(base_path('ai-service/chair.jpg'), 'receipt.jpg', 'image/jpeg', null, true),
            ])->assertRedirect(route('payments.index'))->assertSessionHasNoErrors();
            $this->get($details)->assertOk()->assertDontSee('Assigned Driver')->assertDontSee('TEST-418');
            $proof = $booking->payments()->latest('id')->firstOrFail();
            $this->actingAs($admin)->post(route('admin.payments.approve', $proof), ['amount' => $amount])
                ->assertRedirect(route('admin.payments.index'))->assertSessionHasNoErrors();
            if ($index === 0) {
                $this->actingAs($customer)->getJson($details)->assertOk()
                    ->assertJsonPath('status', 'awaiting_deposit')->assertJsonMissingPath('driver')->assertJsonMissingPath('vehicle');
                $this->assertNull($booking->refresh()->driver_id);
                $this->assertSame('3.000', $booking->remainingDeposit());
                $this->get($paymentUrl)->assertOk()->assertSee('value="3.000"', false);
            }
        }

        $this->assertNotNull($booking->refresh()->deposit_verified_at);
        $this->assertEquals($driver->id, $booking->driver_id);
        $this->assertEquals($vehicle->id, $booking->vehicle_id);
        $this->actingAs($customer)->getJson($details)->assertOk()
            ->assertJsonPath('status', 'pending')->assertJsonMissingPath('driver')->assertJsonMissingPath('vehicle');
        $this->get($details)->assertOk()->assertSee('Waiting for driver confirmation')
            ->assertDontSee('Assigned Driver')->assertDontSee('TEST-418');
        $this->actingAs($driver->user)->patchJson(route('driver.bookings.accept', $booking))
            ->assertOk()->assertJsonPath('status', 'confirmed');
        $this->patchJson(route('driver.bookings.accept', $booking))->assertOk()->assertJsonPath('status', 'confirmed');

        $this->actingAs($customer)->get($details)->assertOk()
            ->assertSee('Assigned vehicle and driver')->assertSee('Assigned Driver')->assertSee('Assigned Van')
            ->assertSee('TEST-418')->assertSee('tel:0791234567');
        $this->getJson($details)->assertOk()->assertJsonPath('status', 'confirmed')
            ->assertJsonPath('driver.phone', '0791234567')->assertJsonPath('vehicle.plate_number', 'TEST-418');
    }

    public function test_moving_price_is_available_before_cliq_checkout_is_configured(): void
    {
        config([
            'booking.osrm_base_url' => 'https://routes.example.test',
            'booking.deposit_percent' => null,
            'booking.commission_percent' => null,
            'booking.deposit_minutes' => null,
            'payments.clik_name' => null,
            'payments.clik_id' => null,
        ]);
        Http::fake(['routes.example.test/*' => Http::response(['routes' => [['distance' => 2500]]])]);
        $customer = User::factory()->create();
        $type = VehicleType::factory()->create(['is_active' => true]);
        Vehicle::factory()->for($type)->create([
            'status' => 'active', 'base_price' => '12.000', 'price_per_km' => '2.000',
        ]);
        $service = Service::factory()->create(['type' => Service::TYPE_TRANSPORT, 'unit' => 'fixed', 'price' => '3.000', 'is_active' => true]);
        $booking = Booking::factory()->for($customer, 'customer')->create([
            'booking_method' => 'quick', 'status' => 'draft', 'driver_id' => null,
            'preferred_vehicle_type_id' => $type->id, 'scheduled_start_at' => now()->addDay(),
            'pickup_latitude' => '31.91', 'pickup_longitude' => '35.9',
            'destination_latitude' => '31.98', 'destination_longitude' => '35.92',
        ]);
        $booking->bookingServices()->create([
            'service_id' => $service->id, 'service_name' => $service->name,
            'unit' => 'fixed', 'quantity' => 1, 'unit_price' => '3.000',
        ]);

        $this->actingAs($customer)->get(route('booking.show', $booking))->assertOk()
            ->assertSee('data-workflow-form="quote"', false)->assertDontSee('Calculate quote')->assertSee('CliQ checkout is temporarily unavailable')
            ->assertDontSee('Continue to CliQ payment');
        $this->postJson(route('bookings.quote', $booking))->assertOk()
            ->assertJsonPath('total_price', '20.000')->assertJsonPath('deposit_amount', null);
        $this->postJson(route('bookings.submit', $booking), ['expected_total' => '20.000'])->assertServiceUnavailable()
            ->assertJsonPath('message', 'CliQ checkout is temporarily unavailable. Your draft is saved.');
        $this->assertSame('draft', $booking->refresh()->status);
        $this->assertNull($booking->total_price);
    }

    public function test_paid_booking_retries_until_a_driver_accepts(): void
    {
        $customer = User::factory()->create();
        $type = VehicleType::factory()->create(['is_active' => true]);
        $booking = Booking::factory()->for($customer, 'customer')->create([
            'driver_id' => null, 'status' => 'pending', 'deposit_verified_at' => now(),
            'preferred_vehicle_type_id' => $type->id, 'scheduled_start_at' => now()->addDay(),
            'deposit_amount' => '4.000',
        ]);
        $details = route('booking.show', $booking);
        $this->actingAs($customer)->get($details)->assertOk()
            ->assertSee('Finding a driver')->assertSee('Check availability')->assertDontSee('Pay deposit via CliQ');
        $this->artisan('bookings:match-paid')->assertSuccessful();
        $this->assertNull($booking->refresh()->driver_id);

        $first = Vehicle::factory()->for($type)->create(['status' => 'active']);
        $this->post(route('bookings.match-driver', $booking))->assertRedirect($details);
        $this->assertEquals($first->driver_id, $booking->refresh()->driver_id);
        $booking->update(['driver_offer_expires_at' => now()->subMinute()]);
        $this->actingAs($first->driver->user)->patchJson(route('driver.bookings.accept', $booking))->assertConflict();

        $second = Vehicle::factory()->for($type)->create(['status' => 'active']);
        $this->artisan('bookings:match-paid')->assertSuccessful();
        $this->assertEquals($second->driver_id, $booking->refresh()->driver_id);
        $this->actingAs($second->driver->user)->patchJson(route('driver.bookings.reject', $booking), [
            'rejection_reason' => 'Unavailable',
        ])->assertOk();
        $this->assertNull($booking->refresh()->driver_id);
        $this->assertNotNull($booking->deposit_verified_at);

        $third = Vehicle::factory()->for($type)->create(['status' => 'active']);
        $this->artisan('bookings:match-paid')->assertSuccessful();
        $this->assertEquals($third->driver_id, $booking->refresh()->driver_id);
        $this->actingAs($third->driver->user)->patchJson(route('driver.bookings.accept', $booking))
            ->assertOk()->assertJsonPath('status', 'confirmed');
    }

    public function test_manual_and_scan_drafts_use_confirmed_editable_inventory_for_recommendations(): void
    {
        $customer = User::factory()->create();
        $type = VehicleType::factory()->create(['name' => 'Medium van', 'is_active' => true]);
        $small = VehicleType::factory()->create(['name' => 'Small pickup', 'is_active' => true]);
        $driver = DriverProfile::factory()->create();
        Vehicle::factory()->for($type)->for($driver, 'driver')->create(['status' => 'active', 'capacity_kg' => 'medium', 'capacity_m3' => 'medium']);
        Vehicle::factory()->for($small)->for($driver, 'driver')->create(['status' => 'active', 'capacity_kg' => 'small', 'capacity_m3' => 'small']);

        $this->actingAs($customer);
        $manual = $this->post(route('booking.store'), ['booking_method' => 'manual']);
        $booking = $customer->bookings()->sole();
        $inventory = route('booking.show', $booking).'#inventory';
        $manual->assertRedirect($inventory);
        $sizes = route('booking.edit', ['booking' => $booking, 'step' => 'type']);
        $this->get($sizes)->assertRedirect($inventory);
        $suggestion = $booking->inventoryItems()->create(['item_type' => 'sofa', 'quantity' => 2, 'source' => 'ai', 'status' => 'suggested']);
        $this->get($sizes)->assertRedirect($inventory);
        $this->putJson(route('bookings.inventory.update', [$booking, $suggestion]), [
            'item_type' => 'sofa', 'quantity' => 2, 'status' => 'confirmed',
            'estimated_weight_kg' => '160.00', 'estimated_volume_m3' => '3.000',
        ])->assertOk()->assertJsonPath('status', 'confirmed');
        $this->get($sizes)->assertOk()->assertSee('320.0 kg')->assertSee('6.00 m³')
            ->assertViewHas('recommendedTypes', [$type->id]);

        $scan = $this->post(route('booking.store'), ['booking_method' => 'scan']);
        $scanDraft = $customer->bookings()->where('booking_method', 'scan')->sole();
        $scan->assertRedirect(route('scan-sessions.workspace', $scanDraft));
        $this->get(route('scan-sessions.workspace', $scanDraft))->assertOk()->assertSee('Review inventory');
    }
}
