<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\DriverProfile;
use App\Models\MoveItNotification;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class PaidBookingMatcher
{
    public function findDriver(int $bookingId): void
    {
        DB::transaction(function () use ($bookingId) {
            $booking = Booking::lockForUpdate()->find($bookingId);
            if ($booking?->status !== 'pending' || !$booking->deposit_verified_at || !$booking->scheduled_start_at?->isFuture()) {
                return;
            }

            if ($booking->driver_id) {
                if ($booking->driver_offer_expires_at?->isFuture()) {
                    return;
                }
                $this->skipCurrentDriver($booking);
            }

            if (!$booking->preferred_vehicle_type_id) {
                return;
            }

            $start = $booking->scheduled_start_at;
            $end = $booking->scheduled_end_at ?? Booking::slotEnd($start);
            $candidates = Vehicle::availableAt($start, $end)
                ->where('vehicle_type_id', $booking->preferred_vehicle_type_id)
                ->whereNotIn('driver_id', DB::table('booking_driver_skips')->where('booking_id', $booking->id)->select('driver_id'))
                ->orderByRaw('base_price + price_per_km * ?', [(float) $booking->distance_km])
                ->orderBy('id')
                ->get(['id', 'driver_id']);

            foreach ($candidates as $candidate) {
                $driver = DriverProfile::with('user')->lockForUpdate()->find($candidate->driver_id);
                $vehicle = Vehicle::with('vehicleType')->lockForUpdate()->find($candidate->id);
                if (!$driver || $driver->status !== 'approved' || !$driver->user?->is_active
                    || !$vehicle || $vehicle->driver_id !== $driver->id || $vehicle->status !== 'active'
                    || !$vehicle->vehicleType?->is_active || (int) $vehicle->vehicle_type_id !== (int) $booking->preferred_vehicle_type_id) {
                    continue;
                }
                if (Booking::whereKeyNot($booking->id)
                    ->where(fn ($query) => $query->where('vehicle_id', $vehicle->id)->orWhere('driver_id', $driver->id))
                    ->occupyingWindow($start, $end)->lockForUpdate()->first(['id'])) {
                    continue;
                }

                $booking->update([
                    'driver_id' => $driver->id,
                    'vehicle_id' => $vehicle->id,
                    'scheduled_end_at' => $end,
                    'driver_offer_expires_at' => now()->addMinutes(config('booking.driver_offer_minutes'))->min($start),
                ]);
                MoveItNotification::bookingEvent($driver->user_id, $booking, 'A paid booking is available for you to accept.');
                return;
            }
        }, 3);
    }

    public function skipCurrentDriver(Booking $booking): void
    {
        DB::table('booking_driver_skips')->insertOrIgnore(['booking_id' => $booking->id, 'driver_id' => $booking->driver_id]);
        $booking->update(['driver_id' => null, 'vehicle_id' => null, 'driver_offer_expires_at' => null]);
    }
}
