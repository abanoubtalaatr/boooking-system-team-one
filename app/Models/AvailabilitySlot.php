<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AvailabilitySlot extends Model
{
    use  HasFactory;

    protected $fillable = ["doctor_id", "day", "start_time", "end_time", "is_booked"];

    protected $casts = [
        "day" => "date",
        "is_booked" => "boolean",
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class, "doctor_id");
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class, "slot_id");
    }
}
