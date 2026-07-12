<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Booking model shared by Doctor accept/reject flows and the booking module foundation.
 */
class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'patient_id',
        'doctor_id',
        'slot_id',
        'availability_slot_id',
        'booking_date',
        'booking_time',
        'consultation_type',
        'status',
        'price',
        'payment_status',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime:H:i',
        'status' => BookingStatus::class,
        'price' => 'decimal:2',
        'payment_status' => PaymentStatus::class,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /** doctor_id references doctor_profiles.id in the doctor module ERD. */
    public function doctorProfile(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(AvailabilitySlot::class, 'slot_id');
    }
}
