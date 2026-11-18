<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings');
            $table->foreignId('service_id')->constrained('services');
            $table->string('service_name', 150);
            $table->string('unit', 20);
            $table->decimal('quantity', 8, 2)->default(1);
            $table->decimal('unit_price', 10, 3);
            $table->timestamps();
            $table->unique(['booking_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_services');
    }
};
