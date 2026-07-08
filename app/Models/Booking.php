<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * NOTE: the Booking module (creation flow, checkout, etc.) is built separately per
 * the prompt — this model only carries what the Doctor module's Accept/Reject
 * actions and DoctorResource::average_rating need to reference.
 */
class Booking extends Model
{
    use  HasFactory;

    protected $fillable = [
        "patient_id",
        "doctor_id",
        "slot_id",
        "status",
        "price",
        "payment_status",
    ];

    protected $casts = [
        "status" => BookingStatus::class,
        "price" => "decimal:2",
        "payment_status" => PaymentStatus::class,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, "patient_id");
    }

    /** doctor_id references doctor_profiles.id, not users.id directly (see ERD). */
    public function doctorProfile(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class, "doctor_id");
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(AvailabilitySlot::class, "slot_id");
    }

    // payments()/rating() relations are owned by the Payment/Rating modules,
    // not declared here to avoid depending on models this module doesn't build.
}
