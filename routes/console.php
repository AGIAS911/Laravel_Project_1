<?php

use Illuminate\Foundation\Inspiring;
use App\Models\Booking;
use App\Models\MoveItNotification;
use App\Services\PaidBookingMatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bookings:expire-deposits', function () {
    Booking::where('status', 'awaiting_deposit')->where('deposit_due_at', '<=', now())
        ->select('id')->chunkById(100, function ($bookings) {
            foreach ($bookings as $booking) {
                DB::transaction(function () use ($booking) {
                    $current = Booking::lockForUpdate()->find($booking->id);
                    if ($current?->status === 'awaiting_deposit' && $current->deposit_due_at?->isPast()
                        && !$current->payments()->whereIn('status', ['pending', 'approved'])->exists()) {
                        $current->update(['status' => 'cancelled']);
                        MoveItNotification::bookingEvent($current->customer_id, $current, 'The deposit deadline passed and the booking was cancelled.');
                        if ($current->driver) {
                            MoveItNotification::bookingEvent($current->driver->user_id, $current, 'The deposit deadline passed and the booking was cancelled.');
                        }
                    }
                }, 3);
            }
        });
})->purpose('Release expired booking holds without a pending or approved payment');

Schedule::command('bookings:expire-deposits')->everyMinute()->withoutOverlapping();

Artisan::command('bookings:match-paid', function () {
    Booking::where('status', 'pending')->whereNotNull('deposit_verified_at')
        ->where('scheduled_start_at', '>', now())
        ->where(fn ($query) => $query->whereNull('driver_id')->orWhereNull('driver_offer_expires_at')
            ->orWhere('driver_offer_expires_at', '<=', now()))
        ->select('id')->chunkById(100, function ($bookings) {
            foreach ($bookings as $booking) {
                try {
                    app(PaidBookingMatcher::class)->findDriver($booking->id);
                } catch (\Throwable $error) {
                    report($error);
                    $this->error("Could not match booking #{$booking->id}. The next run will retry.");
                }
            }
        });
})->purpose('Retry matching paid bookings and replace expired driver offers');

Schedule::command('bookings:match-paid')->everyMinute()->withoutOverlapping();
