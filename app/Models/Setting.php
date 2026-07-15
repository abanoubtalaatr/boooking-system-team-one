<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    public const PlatformBookingCommissionPercentage = 'platform_booking_commission_percentage';

    public const PlatformCardCommissionPercentage = 'platform_card_commission_percentage';

    public const PlatformCashCommissionPercentage = 'platform_cash_commission_percentage';

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'label',
        'description',
    ];
}
