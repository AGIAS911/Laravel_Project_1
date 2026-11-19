<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\DriverProfile;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_messages_and_platform_settings_are_available_only_to_admins(): void
    {
        $this->from(route('info.contact'))->post(route('info.contact.store'), [
            'name' => 'Test Customer', 'email' => 'customer@example.test',
            'subject' => 'Availability', 'message' => 'Do you serve Amman?',
        ])->assertRedirect(route('info.contact'));
        $customer = User::factory()->create();
        $this->actingAs($customer)->get(route('admin.inbox'))->assertForbidden();
        $this->get(route('notifications.index'))->assertOk();

        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get(route('admin.inbox'))->assertOk()->assertSee('Do you serve Amman?');
        $booking = Booking::factory()->for($customer, 'customer')->create(['created_at' => now()]);
        $this->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Pending receipts')
            ->assertSee('Bookings in the last 7 days')
            ->assertSee(now('Asia/Amman')->format('d M Y') . ': 1 bookings')
            ->assertSee('Latest bookings')
            ->assertSee(route('booking.show', $booking));
        $this->get(route('admin.reports'))->assertOk()->assertSee('Approved deposits');
        foreach ([
            'admin.users.index', 'admin.driver-approvals', 'vehicle-types.index', 'vehicles.index',
            'services.index', 'booking.index', 'admin.payments.index', 'admin.driver-reviews.index',
            'admin.reports', 'admin.settings', 'admin.inbox', 'notifications.index',
        ] as $screen) {
            $this->get(route($screen))->assertOk()
                ->assertSee('id="admin-sidebar"', false)
                ->assertSee('class="admin-topbar"', false);
        }
        $this->get(route('admin.pricing-rules.index'))->assertOk()->assertSee('New pricing rule');
        $this->from(route('admin.settings'))->put(route('admin.settings.update'), [
            'deposit_percent' => '20', 'commission_percent' => '10', 'deposit_minutes' => '30',
            'clik_name' => 'Test Beneficiary', 'clik_id' => 'TEST',
        ])->assertRedirect(route('admin.settings'))->assertSessionHasNoErrors();
        $this->get(route('admin.settings'))->assertOk()->assertSee('Test Beneficiary');
        $this->assertSame('TEST', \App\Models\PlatformSetting::values()['clik_id']);
    }

    public function test_expiry_releases_only_holds_without_a_pending_receipt(): void
    {
        [$expired, $underReview] = Booking::factory()->count(2)->create([
            'status' => 'awaiting_deposit', 'deposit_due_at' => now()->subMinute(),
        ]);
        $underReview->payments()->create([
            'purpose' => 'deposit', 'amount' => '10.000',
            'receipt_image' => 'receipts/pending.jpg', 'status' => 'pending',
        ]);

        $this->artisan('bookings:expire-deposits')->assertSuccessful();
        $this->assertSame('cancelled', $expired->refresh()->status);
        $this->assertSame('awaiting_deposit', $underReview->refresh()->status);
        $this->assertSame(1, $expired->customer->notifications()->count());
    }

    public function test_admin_pricing_rules_reject_overlapping_windows(): void
    {
        $rule = [
            'name' => 'Morning', 'day_of_week' => null, 'start_time' => '08:00',
            'end_time' => '10:00', 'multiplier' => '1.250', 'is_active' => true,
        ];
        $this->actingAs(User::factory()->create())
            ->postJson(route('admin.pricing-rules.store'), $rule)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->postJson(route('admin.pricing-rules.store'), $rule)->assertCreated();
        $this->postJson(route('admin.pricing-rules.store'), array_replace($rule, [
            'day_of_week' => 1, 'start_time' => '09:00',
        ]))->assertUnprocessable();
        $this->postJson(route('admin.pricing-rules.store'), array_replace($rule, [
            'day_of_week' => 1, 'start_time' => '10:00', 'end_time' => '12:00',
        ]))->assertCreated();
    }

    public function test_booking_cannot_skip_payment_or_double_book_a_driver(): void
    {
        config([
            'booking.google_routes_key' => 'test-key',
            'booking.deposit_percent' => '20',
            'booking.commission_percent' => '10',
            'booking.deposit_minutes' => '30',
        ]);
        Http::fake(['routes.googleapis.com/*' => Http::response(['routes' => [['distanceMeters' => 2500]]])]);
        $customer = User::factory()->create();
        $driver = DriverProfile::factory()->create();
        $vehicle = Vehicle::factory()->for($driver, 'driver')->create([
            'status' => 'active', 'base_price' => '12.000', 'price_per_km' => '2.000',
        ]);
        $vehicle->vehicleType->update(['is_active' => true]);
        $booking = Booking::factory()->for($customer, 'customer')->for($vehicle)->create([
            'driver_id' => null, 'status' => 'draft',
            'pickup_latitude' => 31, 'pickup_longitude' => 35,
            'destination_latitude' => 32, 'destination_longitude' => 35,
            'scheduled_start_at' => now()->addDay(),
            'scheduled_end_at' => now()->addDay()->addHour(),
        ]);
        $transport = Service::factory()->create(['type' => 'transport', 'is_active' => true, 'unit' => 'fixed']);
        $booking->bookingServices()->create([
            'service_id' => $transport->id, 'service_name' => $transport->name,
            'unit' => 'fixed', 'quantity' => 1, 'unit_price' => '3.000',
        ]);
        $window = ['start' => $booking->scheduled_start_at->toISOString(), 'end' => $booking->scheduled_end_at->toISOString()];

        $this->actingAs($customer)->postJson(route('bookings.inventory.store', $booking), [
            'item_type' => 'sofa', 'quantity' => 1,
        ])->assertCreated();
        $this->get(route('booking.show', $booking))->assertOk()->assertSee('Calculate quote');
        $this->postJson(route('bookings.quote', $booking))
            ->assertOk()->assertJsonPath('status', 'draft')->assertJsonPath('total_price', '20.000');
        $this->postJson(route('bookings.submit', $booking), ['expected_total' => '19.000'])->assertConflict();
        $this->getJson(route('bookings.vehicles', $window))->assertOk()->assertJsonPath('data.0.id', $vehicle->id)
            ->assertJsonMissingPath('data.0.plate_number')->assertJsonMissingPath('data.0.driver_id');
        $this->postJson(route('bookings.submit', $booking))
            ->assertOk()->assertJsonPath('status', 'pending')->assertJsonPath('total_price', '20.000');
        $this->actingAs($driver->user)->patchJson(route('driver.bookings.start', $booking))->assertConflict();
        $this->patchJson(route('driver.bookings.accept', $booking))->assertOk()
            ->assertJsonPath('status', 'awaiting_deposit');
        $this->patchJson(route('driver.bookings.accept', $booking))->assertOk();
        $this->get(route('driver.bookings.index', ['view' => 'requests']), ['Accept' => 'text/html'])
            ->assertOk()->assertSee('New requests')->assertDontSee(route('booking.show', $booking));
        $this->get(route('driver.schedule'))->assertOk()->assertSee(route('booking.show', $booking));

        $this->actingAs($customer)->get(route('booking.index', ['status' => 'awaiting_deposit', 'q' => $booking->id]))
            ->assertOk()->assertSee('My bookings')->assertSee(route('booking.show', $booking));
        $this->get(route('booking.index', ['status' => 'draft', 'q' => $booking->id]))
            ->assertOk()->assertDontSee(route('booking.show', $booking));
        $this->get(route('booking.create'))->assertOk()->assertSee('Locations and schedule');
        $this->get(route('payments.index'))->assertOk()->assertSee('Payment proof history');

        $this->actingAs($customer)->get(route('booking.show', $booking))
            ->assertOk()->assertSee(route('payments.create', ['booking_id' => $booking->id]));
        $this->get(route('payments.create', ['booking_id' => $booking->id]))
            ->assertOk()->assertSee('Transfer details')->assertSee(route('payments.store'));

        $this->assertSame(1, $customer->notifications()->count());
        $other = Booking::factory()->for($vehicle)->create([
            'driver_id' => $driver->id, 'status' => 'pending', 'deposit_amount' => '4.000',
            'scheduled_start_at' => $booking->scheduled_start_at, 'scheduled_end_at' => $booking->scheduled_end_at,
        ]);
        $this->actingAs($driver->user)->patchJson(route('driver.bookings.accept', $other))->assertConflict();
        $this->patchJson(route('driver.bookings.complete', $booking))->assertConflict();

        Storage::fake('local');
        $receipt = new UploadedFile(base_path('ai-service/chair.jpg'), 'receipt.jpg', 'image/jpeg', null, true);
        $this->actingAs($customer)->post(route('payments.store'), [
            'booking_id' => $booking->id, 'amount' => '4.000',
            'transfer_reference' => 'Test-123', 'receipt_image' => $receipt,
        ])->assertRedirect(route('payments.index'))->assertSessionHasNoErrors();
        $this->assertSame('awaiting_deposit', $booking->refresh()->status);
        $this->assertTrue(Payment::referenceInUse('test-123'));
        $payment = $booking->payments()->firstOrFail();
        Storage::disk('local')->assertExists($payment->receipt_image);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.payments.show', $payment))->assertOk()
            ->assertSee('Receipt image')->assertSee('Transfer details')
            ->assertSee(route('payments.receipt', $payment));
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('admin.payments.approve', $payment), ['amount' => '4.000'])
            ->assertRedirect(route('admin.payments.index'))->assertSessionHasNoErrors();
        $this->postJson(route('admin.payments.approve', $payment), ['amount' => '4.000'])
            ->assertUnprocessable();
        $this->assertSame('confirmed', $booking->refresh()->status);

        $this->actingAs($driver->user)->patchJson(route('driver.bookings.start', $booking))->assertOk();
        $this->patchJson(route('driver.bookings.complete', $booking))->assertOk()
            ->assertJsonPath('status', 'completed');
    }

    public function test_scanner_pairing_is_single_use_and_close_revokes_device(): void
    {
        config(['scanner.base_url' => 'https://scanner.example.test']);
        $customer = User::factory()->create();
        $booking = Booking::factory()->for($customer, 'customer')->create(['status' => 'draft']);
        $session = $this->actingAs($customer)
            ->postJson(route('scan-sessions.store', $booking), ['room_name' => 'Living room'])
            ->assertCreated()->json();
        $token = substr(parse_url($session['scan_url'], PHP_URL_FRAGMENT), 6);
        $device = $this->postJson('/api/scanner/pair', ['pairing_token' => $token])->assertOk()->json('device_token');
        $this->postJson('/api/scanner/pair', ['pairing_token' => $token])->assertNotFound();
        $items = ['items' => [['item_type' => 'sofa', 'quantity' => 1]]];
        $this->putJson('/api/scanner/inventory', $items)->assertUnauthorized();
        $this->withToken($device);
        for ($frame = 0; $frame < 120; $frame++) {
            $this->getJson('/api/scanner/device')->assertNoContent();
        }
        $this->putJson('/api/scanner/inventory', $items)->assertNoContent();
        $this->putJson('/api/scanner/inventory', ['items' => [['item_type' => 'chair', 'quantity' => 1]]])->assertNoContent();
        $this->assertDatabaseHas('inventory_items', ['booking_id' => $booking->id, 'item_type' => 'sofa', 'status' => 'suggested']);
        $item = $booking->inventoryItems()->firstOrFail();
        $this->assertSame('suggested', $item->status);
        $this->putJson(route('bookings.inventory.update', [$booking, $item]), [
            'item_type' => 'sofa', 'quantity' => 2,
        ])->assertOk()->assertJsonPath('status', 'confirmed');
        $this->putJson('/api/scanner/inventory', $items)->assertNoContent();
        $this->assertSame(2, $item->refresh()->quantity);
        $this->deleteJson(route('scan-sessions.close', [$booking, $session['scan_session_id']]))->assertNoContent();
        $this->getJson('/api/scanner/device')->assertUnauthorized();
    }
}
