<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
//use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// use Laratrust\Traits\HasRolesAndPermissions; // enable if using Laratrust permissions
// on top of the `role` enum column for finer-grained ability checks.

class User extends Authenticatable
{
    use  HasFactory, HasApiTokens, Notifiable;
    // use HasRolesAndPermissions;

    protected $fillable = [
        "created_by",
        "name",
        "email",
        "phone",
        "password",
        "provider",
        "provider_id",
        "role",
        "status",
    ];

    protected $hidden = [
        "password",
        "remember_token",
    ];

    protected $casts = [
        "email_verified_at" => "datetime",
        "role" => UserRole::class,
        "status" => UserStatus::class,
    ];

    // --- Account provenance -------------------------------------------------

    /** The admin who created this account (doctors/admins are never self-registered). */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by");
    }

    /** Accounts this user (an admin) has created. */
    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, "created_by");
    }

    // --- Doctor module -------------------------------------------------------

    public function doctorProfile(): HasOne
    {
        return $this->hasOne(DoctorProfile::class);
    }

    // --- Chat module ---------------------------------------------------------

    public function conversationsAsPatient(): HasMany
    {
        return $this->hasMany(Conversation::class, "patient_id");
    }

    public function conversationsAsDoctor(): HasMany
    {
        return $this->hasMany(Conversation::class, "doctor_id");
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, "sender_id");
    }

    // --- Booking module (model owned elsewhere; relations declared here for convenience) ---

    public function bookingsAsPatient(): HasMany
    {
        return $this->hasMany(Booking::class, "patient_id");
    }

    /** bookings.doctor_id points at doctor_profiles.id, not users.id directly. */
    public function bookingsAsDoctor(): HasManyThrough
    {
        return $this->hasManyThrough(
            Booking::class,
            DoctorProfile::class,
            "user_id",   // FK on doctor_profiles referencing this user
            "doctor_id", // FK on bookings referencing doctor_profiles
        );
    }
}
