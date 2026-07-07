<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'phone',
    'email',
    'password',
    'birthdate',
    'profile_photo',
    'latitude',
    'longitude',
    'verified_at',
])]
#[Hidden(['password', 'remember_token'])]
class Patient extends Authenticatable
{
    /** @use HasFactory<PatientFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public function otps(): HasMany
    {
        return $this->hasMany(PatientOtp::class);
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
