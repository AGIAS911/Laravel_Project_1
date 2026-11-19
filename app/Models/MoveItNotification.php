<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MoveItNotification extends Model
{
    use HasUuids;

    protected $table = 'notifications';

    protected $fillable = ['user_id', 'booking_id', 'type', 'title', 'message', 'url', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function getDataAttribute(): array
    {
        return ['title' => $this->title, 'message' => $this->message, 'url' => $this->url];
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    public static function bookingEvent(int $userId, Booking $booking, string $message): void
    {
        static::create([
            'user_id' => $userId,
            'booking_id' => $booking->id,
            'type' => 'booking',
            'title' => 'Booking #'.$booking->id,
            'message' => $message,
            'url' => route('booking.show', $booking, false),
        ]);
    }
}
