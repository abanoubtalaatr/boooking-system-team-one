<?php

namespace Database\Factories;

use App\Enums\RefundStatus;
use App\Models\Payment;
use App\Models\PaymentRefund;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PaymentRefund>
 */
class PaymentRefundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'payment_id' => Payment::factory(),
            'amount_cents' => 50000,
            'status' => RefundStatus::Pending,
            'reason' => 'test_refund',
            'idempotency_key' => (string) Str::uuid(),
            'requested_at' => now(),
        ];
    }
}
