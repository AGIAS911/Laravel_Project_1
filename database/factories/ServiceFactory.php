<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence,
            'type' => $this->faker->randomElement([
                'Cleaning',
                'Maintenance',
                'Consulting',
            ]),
            'unit' => $this->faker->randomElement([
                'Hour',
                'Day',
                'Service',
            ]),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
