<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'day_of_week',
        'start_time',
        'end_time',
        'multiplier',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'multiplier' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }
}
