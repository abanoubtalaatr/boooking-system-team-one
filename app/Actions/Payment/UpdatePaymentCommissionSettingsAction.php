<?php

namespace App\Actions\Payment;

use App\Models\Setting;
use App\Services\Payments\MoneyCalculator;
use Illuminate\Support\Facades\DB;

class UpdatePaymentCommissionSettingsAction
{
    public function __construct(private readonly MoneyCalculator $money) {}

    /**
     * @param  array{card_commission_percentage: numeric-string, cash_commission_percentage: numeric-string}  $data
     */
    public function handle(array $data): void
    {
        DB::transaction(function () use ($data): void {
            $this->store(
                Setting::PlatformCardCommissionPercentage,
                (string) $data['card_commission_percentage'],
                'نسبة عمولة الدفع بالفيزا',
                'النسبة التي تحصل عليها المنصة من الحجوزات المدفوعة بالبطاقة.',
            );
            $this->store(
                Setting::PlatformCashCommissionPercentage,
                (string) $data['cash_commission_percentage'],
                'نسبة عمولة الدفع كاش',
                'النسبة التي تُخصم من محفظة الطبيب عند تحصيل الحجز كاش.',
            );
        });
    }

    private function store(string $key, string $value, string $label, string $description): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => 'payments',
                'value' => $this->formattedPercentage($value),
                'type' => 'decimal',
                'label' => $label,
                'description' => $description,
            ],
        );
    }

    private function formattedPercentage(string $value): string
    {
        $basisPoints = $this->money->decimalToCents($value);

        return sprintf('%d.%02d', intdiv($basisPoints, 100), $basisPoints % 100);
    }
}
