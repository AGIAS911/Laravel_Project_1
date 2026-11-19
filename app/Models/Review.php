<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function scopeForDriver(Builder $query, int $driverProfileId): Builder
    {
        return $query->whereHas('booking', function (Builder $bookingQuery) use ($driverProfileId) {
            $bookingQuery->where('driver_id', $driverProfileId)
                ->orWhereHas('vehicle', fn (Builder $vehicleQuery) => $vehicleQuery->where('driver_id', $driverProfileId));
        });
    }
}
