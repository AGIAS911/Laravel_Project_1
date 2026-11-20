<?php

namespace Database\Seeders;

use App\Models\MoveItNotification;
use App\Models\User;
use Illuminate\Database\Seeder;

class MoveItNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! User::query()->where('role', 'customer')->exists()) {
            User::factory()->create(['role' => 'customer']);
        }

        User::query()
            ->where('role', 'customer')
            ->each(function (User $user): void {
                if ($user->notifications()->exists()) {
                    return;
                }

                MoveItNotification::factory()
                    ->count(5)
                    ->forUser($user)
                    ->create();
            });
    }
}
