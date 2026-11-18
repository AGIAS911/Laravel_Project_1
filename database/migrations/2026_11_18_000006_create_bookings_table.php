<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles');
            $table->foreignId('driver_id')->nullable()->constrained('driver_profiles');
            $table->decimal('pickup_latitude', 10, 7)->nullable();
            $table->decimal('pickup_longitude', 10, 7)->nullable();
            $table->decimal('destination_latitude', 10, 7)->nullable();
            $table->decimal('destination_longitude', 10, 7)->nullable();
            $table->decimal('distance_km', 10, 2)->nullable();
            $table->smallInteger('pickup_floor')->default(0);
            $table->boolean('pickup_has_elevator')->default(false);
            $table->smallInteger('destination_floor')->default(0);
            $table->boolean('destination_has_elevator')->default(false);
            $table->dateTime('scheduled_start_at')->nullable();
            $table->dateTime('scheduled_end_at')->nullable();
            $table->dateTime('deposit_due_at')->nullable();
            $table->decimal('total_price', 10, 3)->nullable();
            $table->decimal('deposit_amount', 10, 3)->nullable();
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->decimal('commission_amount', 10, 3)->nullable();
            $table->string('status', 30)->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
            $table->index(['driver_id', 'scheduled_start_at']);
            $table->index(['vehicle_id', 'scheduled_start_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
