<?php

namespace App\Services\Payments;

use App\Enums\PaymentMethod;
use App\Models\Setting;
use InvalidArgumentException;

class PlatformCommissionService
{
    public function __construct(private readonly MoneyCalculator $money) {}

    public function bookingCommissionBasisPoints(PaymentMethod $method): int
    {
        $percentage = Setting::query()->where('key', $this->settingKey($method))->value('value')
            ?? Setting::query()->where('key', Setting::PlatformBookingCommissionPercentage)->value('value');

        if ($percentage === null) {
            return $this->fallbackBasisPoints();
        }

        try {
            return max(0, min(10000, $this->money->decimalToCents((string) $percentage)));
        } catch (InvalidArgumentException) {
            return $this->fallbackBasisPoints();
        }
    }

    public function formattedPercentage(PaymentMethod $method): string
    {
        $basisPoints = $this->bookingCommissionBasisPoints($method);

        return sprintf('%d.%02d', intdiv($basisPoints, 100), $basisPoints % 100);
    }

    private function settingKey(PaymentMethod $method): string
    {
        return match ($method) {
            PaymentMethod::Card => Setting::PlatformCardCommissionPercentage,
            PaymentMethod::Cash => Setting::PlatformCashCommissionPercentage,
        };
    }

    private function fallbackBasisPoints(): int
    {
        return max(0, min(10000, (int) config('payments.platform_commission_bps', 0)));
    }
}
