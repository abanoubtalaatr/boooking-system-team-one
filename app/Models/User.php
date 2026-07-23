<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory,  Notifiable, SoftDeletes;

    protected string $guard_name = 'web';

    // protected $attributes = [
    //     'status' => UserStatus::Active->value,
    // ];

    protected $fillable = [
        'created_by',
        'name',
        'email',
        'phone',
        'password',
        'provider',
        'provider_id',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => UserRole::class,
        // 'status' => UserStatus::class,
        'password' => 'hashed',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function doctorProfile(): HasOne
    {
        return $this->hasOne(DoctorProfile::class);
    }

    public function conversationsAsPatient(): HasMany
    {
        return $this->hasMany(Conversation::class, 'patient_id');
    }

    public function conversationsAsDoctor(): HasMany
    {
        return $this->hasMany(Conversation::class, 'doctor_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function bookingsAsPatient(): HasMany
    {
        return $this->hasMany(Booking::class, 'patient_id');
    }

    public function bookingsAsDoctor(): HasManyThrough
    {
        return $this->hasManyThrough(
            Booking::class,
            DoctorProfile::class,
            'user_id',
            'doctor_id',
        );
    }

    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(AvailabilitySlot::class, 'doctor_id');
    }

    public function doctorBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'doctor_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'doctor_id')
            ->where('currency', config('services.paymob.currency', 'EGP'));
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class, 'doctor_id');
    }

    public function walletWithdrawals(): HasMany
    {
        return $this->hasMany(WalletWithdrawal::class, 'doctor_id');
    }

    public function reviewedWalletWithdrawals(): HasMany
    {
        return $this->hasMany(WalletWithdrawal::class, 'reviewed_by');
    }

    public function bookingNoShowReports(): HasMany
    {
        return $this->hasMany(BookingNoShowReport::class, 'doctor_id');
    }

    public function reviewedBookingNoShowReports(): HasMany
    {
        return $this->hasMany(BookingNoShowReport::class, 'reviewed_by');
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['admin', 'super-admin']);
    }

    public function isDoctor(): bool
    {
        return $this->hasRole('doctor');
    }

    public function isSuspended(): bool
    {
        return $this->status === UserStatus::Suspended;
    }

    public function averageRating(): ?float
    {
        return $this->reviews()->avg('rating');
    }
}
