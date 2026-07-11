<?php

namespace App\Models;

use BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PaymentStatus;

class Booking extends Model
{
    protected $fillable = [
        'booking_number',
        'patient_id',
        'doctor_id',
        'availability_slot_id',
        'booking_date',
        'booking_time',
        'consultation_type',
        'price',
        'status',
        'payment_status',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime:H:i',
        'price' => 'decimal:2',
        'status' => BookingStatus::class,
        'payment_status' => PaymentStatus::class,
    ];

    /**
     * Patient who created the booking.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Doctor of the booking.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Reserved availability slot.
     */
    public function slot(): BelongsTo
    {
        return $this->belongsTo(AvailabilitySlot::class, 'availability_slot_id');
    }
}
