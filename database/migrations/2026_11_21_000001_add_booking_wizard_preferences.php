<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_method', 12)->nullable();
            $table->foreignId('preferred_vehicle_type_id')->nullable()->constrained('vehicle_types')->nullOnDelete();
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->decimal('estimated_weight_kg', 8, 2)->nullable();
            $table->decimal('estimated_volume_m3', 8, 3)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', fn (Blueprint $table) => $table->dropColumn(['estimated_weight_kg', 'estimated_volume_m3']));
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('preferred_vehicle_type_id');
            $table->dropColumn('booking_method');
        });
    }
};
