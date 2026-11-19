<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;



class MoveItNotification extends Model
{
    use HasUuids;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'booking_id',
        'type',
        'title',
        'message',
        'url',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function getDataAttribute(): array
    {
        return array_filter([
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'booking_id' => $this->booking_id,
        ], static fn ($value) => $value !== null);
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }

    public static function bookingEvent(
        User $user,
        Booking $booking,
        string $type,
        string $title,
        string $message,
        ?string $url = null
    ): static {
        return $user->notifications()->create([
            'booking_id' => $booking->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'url' => $url,
        ]);
    }
}
