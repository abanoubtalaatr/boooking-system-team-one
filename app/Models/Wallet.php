<?php

namespace App\Models;

use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    /** @use HasFactory<WalletFactory> */
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'currency',
        'balance_cents',
        'payout_blocked',
    ];

    protected $attributes = [
        'balance_cents' => 0,
        'currency' => 'EGP',
        'payout_blocked' => false,
    ];

    protected function casts(): array
    {
        return [
            'payout_blocked' => 'boolean',
        ];
    }

    public function canWithdraw(): bool
    {
        return ! $this->payout_blocked && $this->balance_cents > 0;
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(WalletWithdrawal::class);
    }
}
