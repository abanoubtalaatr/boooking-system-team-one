<?php

namespace Database\Factories;

use App\Enums\WalletWithdrawalStatus;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletWithdrawal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WalletWithdrawal>
 */
class WalletWithdrawalFactory extends Factory
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
            'doctor_id' => User::factory()->doctor(),
            'wallet_id' => fn (array $attributes) => Wallet::factory()->create(['doctor_id' => $attributes['doctor_id']])->id,
            'amount_cents' => 10000,
            'currency' => 'EGP',
            'status' => WalletWithdrawalStatus::PendingReview,
            'idempotency_key' => (string) Str::uuid(),
        ];
    }
}
