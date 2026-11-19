<?php

namespace Database\Seeders;

use App\Models\DriverProfile;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicleTypes = VehicleType::factory()->count(3)->create();
        $drivers = DriverProfile::factory()->count(5)->create();

        Vehicle::factory()
            ->count(10)
            ->state(fn () => [
                'vehicle_type_id' => $vehicleTypes->random()->id,
                'driver_id' => $drivers->random()->id,
            ])
            ->create();
    }
}
