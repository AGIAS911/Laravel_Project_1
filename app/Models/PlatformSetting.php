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

    public static function bookingConfigured(): bool
    {
        $settings = static::values();

        return filled(config('booking.google_routes_key'))
            && filled($settings['deposit_percent'])
            && filled($settings['commission_percent'])
            && filled($settings['deposit_minutes'])
            && static::cliqConfigured($settings);
    }

    public static function cliqConfigured(?array $settings = null): bool
    {
        $settings ??= static::values();

        return filled($settings['clik_name']) && filled($settings['clik_id']);
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
