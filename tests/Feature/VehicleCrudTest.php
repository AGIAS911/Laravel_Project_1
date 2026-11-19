<?php

namespace Tests\Feature;

use App\Models\DriverProfile;
use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_vehicle_pages_render(): void
    {
        [$vehicle, $vehicleType, $driver] = $this->createVehicle();

        $this->get(route('vehicles.index'))->assertOk();
        $this->get(route('vehicles.create'))->assertOk();
        $this->get(route('vehicles.show', $vehicle))
            ->assertOk()
            ->assertSee('Test Driver');
        $this->get(route('vehicles.edit', $vehicle))->assertOk();
    }

    public function test_vehicles_can_be_created_updated_and_deleted(): void
    {
        [, $vehicleType, $driver] = $this->createVehicle();

        $this->post(route('vehicles.store'), [
            'vehicle_type_id' => $vehicleType->id,
            'driver_id' => $driver->id,
            'name' => 'Delivery van',
            'plate_number' => 'VAN-001',
            'capacity_kg' => 'medium',
            'capacity_m3' => 'large',
            'base_price' => '12.500',
            'price_per_km' => '1.250',
            'status' => 'active',
        ])->assertRedirect(route('vehicles.index'));

        $vehicle = Vehicle::where('plate_number', 'VAN-001')->firstOrFail();
        $this->assertSame('medium', $vehicle->capacity_kg);
        $this->assertSame('large', $vehicle->capacity_m3);

        $this->put(route('vehicles.update', $vehicle), [
            'vehicle_type_id' => $vehicleType->id,
            'driver_id' => $driver->id,
            'name' => 'Updated van',
            'plate_number' => 'VAN-001',
            'capacity_kg' => 'large',
            'capacity_m3' => 'medium',
            'base_price' => '15.000',
            'price_per_km' => '1.500',
            'status' => 'maintenance',
        ])->assertRedirect(route('vehicles.index'));

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'name' => 'Updated van',
            'capacity_kg' => 'large',
            'capacity_m3' => 'medium',
            'status' => 'maintenance',
        ]);

        $this->delete(route('vehicles.destroy', $vehicle))
            ->assertRedirect(route('vehicles.index'));

        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
    }

    public function test_vehicle_with_bookings_cannot_be_deleted(): void
    {
        [$vehicle] = $this->createVehicle();
        Booking::create([
            'customer_id' => User::factory()->create()->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $this->delete(route('vehicles.destroy', $vehicle))
            ->assertRedirect(route('vehicles.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id]);
    }

    private function createVehicle(): array
    {
        $vehicleType = VehicleType::create([
            'name' => 'Van',
            'is_active' => true,
        ]);
        $driver = DriverProfile::create([
            'user_id' => User::factory()->create(['name' => 'Test Driver'])->id,
            'license_number' => 'LICENSE-1',
            'license_image' => 'license.png',
        ]);
        $vehicle = Vehicle::create([
            'vehicle_type_id' => $vehicleType->id,
            'driver_id' => $driver->id,
            'name' => 'Test vehicle',
            'plate_number' => 'TEST-001',
            'capacity_kg' => 'small',
            'capacity_m3' => 'small',
            'base_price' => 0,
            'price_per_km' => 0,
            'status' => 'active',
        ]);

        return [$vehicle, $vehicleType, $driver];
    }
}
