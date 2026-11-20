<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $statuses = [
            'draft',
            'pending',
            'awaiting_deposit',
            'confirmed',
            'in_progress',
            'completed',
            'cancelled',
            'rejected',
        ];

        $existing = DB::table('bookings')->distinct()->pluck('status')->all();
        $invalid = array_diff($existing, $statuses);

        if ($invalid !== []) {
            throw new RuntimeException('Unsupported booking statuses: '.implode(', ', $invalid));
        }

        Schema::table('bookings', function (Blueprint $table) use ($statuses) {
            $table->enum('status', $statuses)->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('status', 30)->default('draft')->change();
        });
    }
};
