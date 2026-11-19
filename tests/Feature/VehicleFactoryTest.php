<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use Database\Seeders\VehicleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicle_factory_creates_related_type_and_driver(): void
    {
        $vehicle = Vehicle::factory()->create();

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'vehicle_type_id' => $vehicle->vehicle_type_id,
            'driver_id' => $vehicle->driver_id,
        ]);
        $this->assertDatabaseHas('vehicle_types', ['id' => $vehicle->vehicle_type_id]);
        $this->assertDatabaseHas('driver_profiles', ['id' => $vehicle->driver_id]);
    }

    public function test_vehicle_seeder_creates_vehicles_with_dependencies(): void
    {
        $this->seed(VehicleSeeder::class);

        $this->assertDatabaseCount('vehicle_types', 3);
        $this->assertDatabaseCount('driver_profiles', 5);
        $this->assertDatabaseCount('vehicles', 10);
    }
}
