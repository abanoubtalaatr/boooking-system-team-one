<?php

namespace App\Models;

use App\Enums\UserRole;
//use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        "name",
        "email",
       // "phone",
        "password",
        "role",
    ];

    protected $hidden = [
        "password",
        "remember_token",
    ];

    protected $casts = [
        "email_verified_at" => "datetime",
        "role" => UserRole::class,
    ];

   

    

    // --- Doctor module -------------------------------------------------------

    public function doctorProfile(): HasOne
    {
        return $this->hasOne(DoctorProfile::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'doctor_id', 'user_id');
    }
    
    public function messages()
    {
        return $this->morphMany(Message::class, 'sender');
    }

    // --- Chat module ---------------------------------------------------------

  /*  public function conversationsAsPatient(): HasMany
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
*/
    }