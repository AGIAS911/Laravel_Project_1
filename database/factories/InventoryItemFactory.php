<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InventoryItem> */
class InventoryItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(), 'item_type' => fake()->randomElement(['chair', 'sofa', 'bed']),
            'quantity' => fake()->numberBetween(1, 4), 'source' => 'manual', 'status' => 'confirmed',
        ];
    }
}
