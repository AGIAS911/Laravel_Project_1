<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    public $timestamps = false;

    protected $fillable = ['id', 'settings'];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }

    public static function values(): array
    {
        return (static::find(1)?->settings ?? []) + [
            'deposit_percent' => config('booking.deposit_percent'),
            'commission_percent' => config('booking.commission_percent'),
            'deposit_minutes' => config('booking.deposit_minutes'),
            'clik_name' => config('payments.clik_name'),
            'clik_id' => config('payments.clik_id'),
        ];
    }
}
