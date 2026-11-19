<?php

namespace Database\Factories;

use App\Models\DriverProfile;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'vehicle_type_id' => VehicleType::factory(),
            'driver_id' => DriverProfile::factory(),
            'name' => fake()->randomElement(['Van', 'Truck', 'Pickup', 'Box Truck']),
            'plate_number' => fake()->unique()->bothify('???-####'),
            'image' => null,
            'capacity_kg' => fake()->randomElement(['small', 'medium', 'large']),
            'capacity_m3' => fake()->randomElement(['small', 'medium', 'large']),
            'base_price' => fake()->randomFloat(3, 50, 1000),
            'price_per_km' => fake()->randomFloat(3, 0.5, 20),
            'status' => fake()->randomElement(['active', 'inactive', 'maintenance']),
        ];
    }
}
