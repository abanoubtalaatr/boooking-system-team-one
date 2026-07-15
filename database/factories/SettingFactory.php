<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => 'general',
            'key' => fake()->unique()->slug(3),
            'value' => fake()->word(),
            'type' => 'string',
            'label' => fake()->sentence(3),
            'description' => fake()->sentence(),
        ];
    }

    public function platformBookingCommission(string $percentage = '10.00'): static
    {
        return $this->state(fn (): array => [
            'group' => 'payments',
            'key' => Setting::PlatformBookingCommissionPercentage,
            'value' => $percentage,
            'type' => 'decimal',
            'label' => 'نسبة عمولة المنصة على الحجوزات',
        ]);
    }

    public function platformCardCommission(string $percentage = '10.00'): static
    {
        return $this->paymentCommission(Setting::PlatformCardCommissionPercentage, 'نسبة عمولة الدفع بالفيزا', $percentage);
    }

    public function platformCashCommission(string $percentage = '10.00'): static
    {
        return $this->paymentCommission(Setting::PlatformCashCommissionPercentage, 'نسبة عمولة الدفع كاش', $percentage);
    }

    private function paymentCommission(string $key, string $label, string $percentage): static
    {
        return $this->state(fn (): array => [
            'group' => 'payments',
            'key' => $key,
            'value' => $percentage,
            'type' => 'decimal',
            'label' => $label,
        ]);
    }
}
