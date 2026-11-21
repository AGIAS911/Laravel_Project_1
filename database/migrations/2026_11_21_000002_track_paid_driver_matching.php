<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dateTime('deposit_verified_at')->nullable();
            $table->dateTime('driver_offer_expires_at')->nullable();
            $table->index(['status', 'deposit_verified_at']);
        });

        Schema::create('booking_driver_skips', function (Blueprint $table) {
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('driver_profiles')->cascadeOnDelete();
            $table->primary(['booking_id', 'driver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_driver_skips');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['status', 'deposit_verified_at']);
            $table->dropColumn(['deposit_verified_at', 'driver_offer_expires_at']);
        });
    }
};
