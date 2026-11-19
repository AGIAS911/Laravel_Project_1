<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'driver_id' => DriverProfile::factory(),
            'status' => 'completed',
        ];
    }
}
