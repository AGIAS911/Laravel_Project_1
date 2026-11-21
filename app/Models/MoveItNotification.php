<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\DatabaseNotification;

class MoveItNotification extends DatabaseNotification
{
    use HasFactory, HasUuids;

    public function getTitleAttribute($value = null): ?string
    {
        $title = $value ?? ($this->data['title'] ?? null);

        return $title !== null
            ? preg_replace('/\bBooking #(?=\d)/', 'Booking ', (string) $title)
            : null;
    }

    public function getMessageAttribute($value = null): ?string
    {
        return $value ?? ($this->data['message'] ?? null);
    }

    public function getUrlAttribute($value = null): ?string
    {
        return $value ?? ($this->data['url'] ?? null);
    }

    public static function bookingEvent(int $userId, Booking $booking, string $message): static
    {
        $user = User::findOrFail($userId);

        return $user->notifications()->create([
            'type' => 'booking',
            'title' => 'Booking '.$booking->id,
            'message' => $message,
            'url' => route('booking.show', $booking, false),
            'data' => [
                'title' => 'Booking #'.$booking->id,
                'message' => $message,
                'url' => route('booking.show', $booking, false),
                'booking_id' => $booking->id,
            ],
        ]);
    }
}