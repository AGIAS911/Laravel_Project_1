<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

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
        'active_transfer_reference',
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
        $reference = $reference === null ? '' : mb_strtoupper(trim($reference));
        if ($reference === '') {
            return false;
        }

        return static::where('active_transfer_reference', $reference)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists();
    }
}
