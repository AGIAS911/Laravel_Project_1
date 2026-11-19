<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory()->state(['status' => 'awaiting_deposit']),
            'purpose' => Payment::PURPOSE_DEPOSIT, 'amount' => '20.000',
            'transfer_reference' => fake()->unique()->uuid(), 'receipt_image' => 'receipts/demo-sample.jpg',
            'status' => Payment::STATUS_PENDING,
        ];
    }
}
