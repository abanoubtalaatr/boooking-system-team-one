<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorProfile extends Model
{
    use  HasFactory;

    protected $fillable = [
        "user_id",
        "bio",
        "consultation_price",
        "is_approved",
    ];

    protected $casts = [
        "consultation_price" => "decimal:2",
        "is_approved" => "boolean",
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, "doctor_specialty");
    }

    public function hospitals(): BelongsToMany
    {
        return $this->belongsToMany(Hospital::class, "doctor_hospital");
    }

    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(AvailabilitySlot::class, "doctor_id");
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, "doctor_id");
    }
}
