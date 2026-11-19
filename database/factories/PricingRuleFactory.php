<?php

namespace Database\Factories;

use App\Models\PricingRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PricingRule> */
class PricingRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true), 'day_of_week' => 1,
            'start_time' => '08:00:00', 'end_time' => '12:00:00', 'multiplier' => '1.100', 'is_active' => false,
        ];
    }
}
