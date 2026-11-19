<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('notifiable_type')->nullable()->change();
            $table->unsignedBigInteger('notifiable_id')->nullable()->change();
            $table->json('data')->nullable()->change();
            $table->index(['user_id', 'read_at']);
        });

        DB::table('notifications')->where('notifiable_type', App\Models\User::class)
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $data = json_decode($row->data, true) ?? [];
                    DB::table('notifications')->where('id', $row->id)->update([
                        'user_id' => DB::table('users')->where('id', $row->notifiable_id)->value('id'),
                        'title' => $data['title'] ?? null,
                        'message' => $data['message'] ?? null,
                        'url' => $data['url'] ?? null,
                    ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('notifications')->whereNull('notifiable_id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('notifications')->where('id', $row->id)->update([
                    'notifiable_type' => App\Models\User::class,
                    'notifiable_id' => $row->user_id,
                    'data' => json_encode(['title' => $row->title, 'message' => $row->message, 'url' => $row->url]),
                ]);
            }
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'read_at']);
            $table->dropConstrainedForeignId('booking_id');
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['title', 'message', 'url']);
            $table->string('notifiable_type')->nullable(false)->change();
            $table->unsignedBigInteger('notifiable_id')->nullable(false)->change();
            $table->json('data')->nullable(false)->change();
        });
    }
};
