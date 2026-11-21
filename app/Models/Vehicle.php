<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_type_id',
        'driver_id',
        'name',
        'plate_number',
        'image',
        'capacity_kg',
        'capacity_m3',
        'base_price',
        'price_per_km',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:3',
            'price_per_km' => 'decimal:3',
        ];
    }

    public function scopeEligibleForBooking(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereHas('vehicleType', fn ($type) => $type->where('is_active', true))
            ->whereHas('driver', fn ($driver) => $driver->where('status', 'approved')
                ->whereHas('user', fn ($user) => $user->where('is_active', true)));
    }

    public function scopeAvailableAt(Builder $query, Carbon $start, Carbon $end): Builder
    {
        $overlap = fn ($query) => $query->occupyingWindow($start, $end);

        return $query->eligibleForBooking()
            ->whereDoesntHave('bookings', $overlap)
            ->whereDoesntHave('driver.bookings', $overlap);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DriverProfile::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
