<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\DriverProfile;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = DriverProfile::query()
            ->orderBy('id')
            ->limit(5)
            ->get();

        if ($drivers->isEmpty()) {
            $drivers = DriverProfile::factory()->count(5)->create();
        }

        foreach ($drivers as $driver) {
            $reviewsToCreate = max(0, 3 - Review::forDriver($driver->id)->count());

            for ($index = 0; $index < $reviewsToCreate; $index++) {
                $booking = Booking::factory()->create([
                    'driver_id' => $driver->id,
                    'status' => 'completed',
                ]);
                Review::factory()->for($booking)->create();
            }
        }
    }
}
