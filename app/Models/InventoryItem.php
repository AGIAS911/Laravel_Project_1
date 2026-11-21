<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    use HasFactory;

    protected $hidden = ['image'];

    protected $fillable = [
        'booking_id',
        'scan_session_id',
        'item_type',
        'estimated_weight_kg',
        'estimated_volume_m3',
        'quantity',
        'image',
        'source',
        'status',
        'tracking_key',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function estimate(): array
    {
        [$weight, $volume] = config('inventory.estimates')[$this->item_type] ?? [15, 0.25];

        return [
            (float) ($this->estimated_weight_kg ?? $weight),
            (float) ($this->estimated_volume_m3 ?? $volume),
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function scanSession(): BelongsTo
    {
        return $this->belongsTo(ScanSession::class);
    }
}
