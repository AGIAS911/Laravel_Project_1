<?php

namespace Database\Factories;

use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DriverProfile>
 */
class DriverProfileFactory extends Factory
{
    protected $model = DriverProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'driver']),
            'license_number' => fake()->unique()->bothify('LIC-########'),
            'license_image' => 'licenses/'.Str::uuid().'.jpg',
            'status' => 'approved',
        ];
    }
}
