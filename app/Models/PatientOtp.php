<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PatientOtpType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'patient_id',
    'phone',
    'code',
    'type',
    'expires_at',
    'used_at',
])]
class PatientOtp extends Model
{
    use HasFactory;

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PatientOtpType::class,
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }
}
