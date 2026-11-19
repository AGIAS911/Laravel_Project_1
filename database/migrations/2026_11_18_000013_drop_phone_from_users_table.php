<?php

use Illuminate\Database\Migrations\Migration;
return new class extends Migration
{
    public function up(): void
    {
        // Keep existing phone numbers. The follow-up migration repairs databases that already ran the original drop.
    }

    public function down(): void
    {
        // The original users migration owns this column.
    }
};
