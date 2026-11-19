<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\DatabaseNotification;

class MoveItNotification extends DatabaseNotification
{
    use HasUuids;

    public function getTitleAttribute(): ?string
    {
        return $this->data['title'] ?? null;
    }

    public function getMessageAttribute(): ?string
    {
        return $this->data['message'] ?? null;
    }

    public function getUrlAttribute(): ?string
    {
        return $this->data['url'] ?? null;
    }

    public static function bookingEvent(int $userId, Booking $booking, string $message): static
    {
        $user = User::findOrFail($userId);

        return $user->notifications()->create([
            'type' => 'booking',
            'data' => [
                'title' => 'Booking #'.$booking->id,
                'message' => $message,
                'url' => route('booking.show', $booking, false),
                'booking_id' => $booking->id,
            ],
        ]);
    }
}
