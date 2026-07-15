<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'booking_id',
        'patient_id',
        'doctor_id',
        'method',
        'status',
        'amount_cents',
        'currency',
        'commission_bps',
        'commission_amount_cents',
        'doctor_amount_cents',
        'idempotency_key',
        'provider',
        'provider_intention_id',
        'provider_order_id',
        'provider_transaction_id',
        'checkout_url',
        'provider_client_secret',
        'failure_code',
        'failure_message',
        'paid_at',
        'failed_at',
        'expires_at',
        'refunded_at',
    ];

    protected $hidden = [
        'provider_client_secret',
    ];

    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'provider_client_secret' => 'encrypted',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'expires_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class);
    }
}
