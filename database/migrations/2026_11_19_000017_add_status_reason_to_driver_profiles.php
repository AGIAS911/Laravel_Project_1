<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_profiles', fn (Blueprint $table) => $table->text('status_reason')->nullable());
    }

    public function down(): void
    {
        Schema::table('driver_profiles', fn (Blueprint $table) => $table->dropColumn('status_reason'));
    }
};
