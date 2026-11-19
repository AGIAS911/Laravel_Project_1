<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $normalize = "NULLIF(UPPER(TRIM(transfer_reference)), '')";
        $duplicates = DB::table('payments')->where('status', '!=', 'rejected')
            ->whereRaw("$normalize IS NOT NULL")->selectRaw('COUNT(*) AS reference_count')
            ->groupByRaw($normalize)->havingRaw('COUNT(*) > 1');
        if (DB::query()->fromSub($duplicates, 'duplicates')->exists()) {
            throw new RuntimeException('Resolve duplicate active payment references before migrating.');
        }

        Schema::table('payments', function (Blueprint $table) use ($normalize) {
            $table->string('active_transfer_reference', 100)
                ->virtualAs("CASE WHEN status <> 'rejected' THEN $normalize ELSE NULL END")->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['active_transfer_reference']);
            $table->dropColumn('active_transfer_reference');
        });
    }
};
