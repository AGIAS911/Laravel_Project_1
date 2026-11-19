<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'driver_id',
        'pickup_latitude',
        'pickup_longitude',
        'destination_latitude',
        'destination_longitude',
        'distance_km',
        'pickup_floor',
        'pickup_has_elevator',
        'destination_floor',
        'destination_has_elevator',
        'scheduled_start_at',
        'scheduled_end_at',
        'deposit_due_at',
        'total_price',
        'deposit_amount',
        'commission_rate',
        'commission_amount',
        'status',
        'rejection_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'pickup_latitude' => 'decimal:7',
            'pickup_longitude' => 'decimal:7',
            'destination_latitude' => 'decimal:7',
            'destination_longitude' => 'decimal:7',
            'distance_km' => 'decimal:2',
            'pickup_floor' => 'integer',
            'pickup_has_elevator' => 'boolean',
            'destination_floor' => 'integer',
            'destination_has_elevator' => 'boolean',
            'scheduled_start_at' => 'datetime',
            'scheduled_end_at' => 'datetime',
            'deposit_due_at' => 'datetime',
            'total_price' => 'decimal:3',
            'deposit_amount' => 'decimal:3',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:3',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DriverProfile::class);
    }

    public function bookingServices(): HasMany
    {
        return $this->hasMany(BookingService::class);
    }

    public function servicesTotal(): string
    {
        $total = $this->bookingServices()->get()
            ->sum(fn ($item) => $item->quantity * $item->unit_price);

        return number_format($total, 3, '.', '');
    }

    public function scanSessions(): HasMany
    {
        return $this->hasMany(ScanSession::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }
}
