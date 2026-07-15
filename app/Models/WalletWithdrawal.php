<?php

namespace App\Models;

use App\Enums\WalletWithdrawalStatus;
use Database\Factories\WalletWithdrawalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletWithdrawal extends Model
{
    /** @use HasFactory<WalletWithdrawalFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'doctor_id',
        'wallet_id',
        'amount_cents',
        'currency',
        'status',
        'idempotency_key',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'balance_before_cents',
        'balance_after_cents',
    ];

    protected $attributes = [
        'status' => 'pending_review',
    ];

    protected function casts(): array
    {
        return [
            'status' => WalletWithdrawalStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
