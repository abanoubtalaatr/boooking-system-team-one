<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
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
            'booking_id' => Booking::factory(),
            'patient_id' => fn (array $attributes) => Booking::query()->findOrFail($attributes['booking_id'])->patient_id,
            'doctor_id' => fn (array $attributes) => Booking::query()->findOrFail($attributes['booking_id'])->doctor_id,
            'method' => PaymentMethod::Card,
            'status' => PaymentStatus::Pending,
            'amount_cents' => 50000,
            'currency' => 'EGP',
            'commission_bps' => 0,
            'commission_amount_cents' => 0,
            'doctor_amount_cents' => 50000,
            'idempotency_key' => (string) Str::uuid(),
            'provider' => 'paymob',
        ];
    }
}
