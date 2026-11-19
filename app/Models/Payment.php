<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const PURPOSE_DEPOSIT = 'deposit';
    public const PURPOSE_BALANCE = 'balance';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'booking_id',
        'purpose',
        'amount',
        'transfer_reference',
        'receipt_image',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $hidden = [
        'receipt_image',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:3',
            'reviewed_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Is the same transfer reference already used by another non-rejected payment?
     */
    public static function referenceInUse(?string $reference, ?int $exceptId = null): bool
    {
        if (! $reference) {
            return false;
        }

        return static::where('transfer_reference', $reference)
            ->where('status', '!=', self::STATUS_REJECTED)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists();
    }
}
