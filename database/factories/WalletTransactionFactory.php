<?php

namespace Database\Factories;

use App\Enums\WalletTransactionType;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WalletTransaction>
 */
class WalletTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'payment_id' => Payment::factory(),
            'booking_id' => fn (array $attributes) => Payment::query()->findOrFail($attributes['payment_id'])->booking_id,
            'type' => WalletTransactionType::Adjustment,
            'amount_cents' => 1000,
            'balance_after_cents' => 1000,
            'idempotency_key' => (string) Str::uuid(),
        ];
    }
}
