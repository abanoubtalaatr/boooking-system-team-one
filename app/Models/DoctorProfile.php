<?php

namespace App\Models;

use App\Http\Concerns\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorProfile extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'user_id', 'specialty_id', 'hospital_id',
        'latitude', 'longitude', 'bio', 'avatar',
        'price', 'experience_years', 'is_active',
    ];

    protected $casts = [
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
        'price'     => 'decimal:2',
        'is_active' => 'boolean',
        'certificates' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialization::class, 'specialization_id');
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    // reviews & rate

    
    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // reviews.user_id بيشاور على نفس user_id بتاع الدكتور
        return $this->hasMany(Review::class, 'user_id', 'user_id');
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating');
    }

    public function availabilitySlots(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AvailabilitySlot::class, 'doctor_id', 'user_id');
    }

    public function favorites(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Favorite::class, 'doctor_id', 'user_id');
    }

    /*public function is_favorite($patient_id)
    {
        return Favorite::query()
            ->where('doctor_id', $this->user_id)
            ->where('user_id', $patient_id)
            ->exists();
    }*/
}