<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings');
            $table->foreignId('scan_session_id')->nullable();
            $table->string('item_type', 100);
            $table->integer('quantity')->default(1);
            $table->string('image')->nullable();
            $table->string('source', 10);
            $table->string('status', 20)->default('suggested');
            $table->string('tracking_key', 150)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['booking_id', 'status']);
            $table->unique(['scan_session_id', 'tracking_key']);
            $table->foreign(['scan_session_id', 'booking_id'])
                ->references(['id', 'booking_id'])->on('scan_sessions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
