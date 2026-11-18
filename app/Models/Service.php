<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    public const TYPE_TRANSPORT = 'transport';
    public const TYPE_WORKERS = 'workers';
    public const TYPE_PACKING = 'packing';
    public const TYPE_BOXES = 'boxes';
    public const TYPE_ASSEMBLY = 'assembly';

    protected $fillable = [
        'name',
        'description',
        'type',
        'unit',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function bookingServices(): HasMany
    {
        return $this->hasMany(BookingService::class);
    }
}
