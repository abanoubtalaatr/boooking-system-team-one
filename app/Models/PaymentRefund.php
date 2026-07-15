<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Database\Factories\PaymentRefundFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRefund extends Model
{
    /** @use HasFactory<PaymentRefundFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'payment_id',
        'amount_cents',
        'status',
        'reason',
        'idempotency_key',
        'provider_refund_id',
        'failure_message',
        'requested_at',
        'completed_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RefundStatus::class,
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
