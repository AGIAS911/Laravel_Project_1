<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
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
            'capacity_kg' => 'decimal:2',
            'capacity_m3' => 'decimal:2',
            'base_price' => 'decimal:3',
            'price_per_km' => 'decimal:3',
        ];
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
