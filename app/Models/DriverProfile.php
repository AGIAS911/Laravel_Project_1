<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DriverProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_number',
        'license_image',
        'status',
    ];

    protected $hidden = [
        'license_image',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'driver_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'driver_id');
    }
}
