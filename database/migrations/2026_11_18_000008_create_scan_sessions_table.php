<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings');
            $table->string('room_name', 100);
            $table->string('status', 20)->default('pending');
            $table->char('pairing_token_hash', 64)->nullable()->unique();
            $table->dateTime('pairing_expires_at')->nullable();
            $table->dateTime('paired_at')->nullable();
            $table->char('device_token_hash', 64)->nullable()->unique();
            $table->dateTime('expires_at');
            $table->timestamps();
            $table->unique(['id', 'booking_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_sessions');
    }
};
