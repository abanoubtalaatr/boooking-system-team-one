<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AvailabilitySlot extends Model
{
    use HasFactory;
    protected $fillable = [
        'doctor_id',
        'day',
        'start_time',
        'end_time',
        'is_booked',
    ];

    protected $casts = [
        'day' => 'date',
        'is_booked' => 'boolean',
    ];

    /**
     * Doctor who owns this slot.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Booking associated with this slot.
     */
    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class, 'availability_slot_id');
    }

}
