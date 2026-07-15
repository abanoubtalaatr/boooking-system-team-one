<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            Setting::PlatformBookingCommissionPercentage => [
                'group' => 'payments',
                'value' => '10.00',
                'type' => 'decimal',
                'label' => 'نسبة عمولة المنصة على الحجوزات',
                'description' => 'النسبة التي تحصل عليها المنصة من قيمة كل حجز، سواء كان الدفع نقداً أو بالبطاقة.',
            ],
            Setting::PlatformCardCommissionPercentage => [
                'group' => 'payments',
                'value' => '10.00',
                'type' => 'decimal',
                'label' => 'نسبة عمولة الدفع بالفيزا',
                'description' => 'النسبة التي تحصل عليها المنصة من الحجوزات المدفوعة بالبطاقة.',
            ],
            Setting::PlatformCashCommissionPercentage => [
                'group' => 'payments',
                'value' => '10.00',
                'type' => 'decimal',
                'label' => 'نسبة عمولة الدفع كاش',
                'description' => 'النسبة التي تُخصم من محفظة الطبيب عند تحصيل الحجز كاش.',
            ],
        ];

        foreach ($settings as $key => $attributes) {
            Setting::query()->firstOrCreate(['key' => $key], $attributes);
        }
    }
}
