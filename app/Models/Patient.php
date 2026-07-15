<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Patient extends Authenticatable
{
    /** @use HasFactory<PatientFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'birthdate',
        'profile_photo',
        'latitude',
        'longitude',
        'verified_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    public function otps(): HasMany
    {
        return $this->hasMany(PatientOtp::class);
    }

    /**
     * Bookings created by the patient.
     */
    public function patientBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'patient_id');
    }

    /**
     * Doctors the patient marked as favorite.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }
}
