<?php

namespace Database\Factories;

use App\Models\MoveItNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MoveItNotification>
 */
class MoveItNotificationFactory extends Factory
{
    protected $model = MoveItNotification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        $message = $this->faker->sentence();

        return [
            'type' => $this->faker->randomElement(['booking', 'driver_status']),
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(),
            'user_id' => null,
            'booking_id' => null,
            'title' => $title,
            'message' => $message,
            'url' => null,
            'data' => [
                'title' => $title,
                'message' => $message,
            ],
            'read_at' => $this->faker->optional(0.3)->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (): array => [
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->getKey(),
            'user_id' => $user->getKey(),
        ]);
    }
}
