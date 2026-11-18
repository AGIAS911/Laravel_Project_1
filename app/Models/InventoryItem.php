<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    protected $fillable = [
        'booking_id',
        'scan_session_id',
        'item_type',
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

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function scanSession(): BelongsTo
    {
        return $this->belongsTo(ScanSession::class);
    }
}
