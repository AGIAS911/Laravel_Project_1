<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_type_id')->constrained('vehicle_types');
            $table->foreignId('driver_id')->constrained('driver_profiles');
            $table->string('name', 150);
            $table->string('plate_number', 30)->unique();
            $table->string('image')->nullable();
            $table->enum('capacity_kg', ['small', 'medium', 'large']);
            $table->enum('capacity_m3', ['small', 'medium', 'large']);
            $table->decimal('base_price', 10, 3);
            $table->decimal('price_per_km', 10, 3);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
