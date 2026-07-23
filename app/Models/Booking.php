<?php
namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\ConsultationType;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Patient booking API + doctor accept/reject flows.
 */
class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'patient_id',
        'doctor_id',
        'availability_slot_id',
        'booking_date',
        'booking_time',
        'consultation_type',
        'status',
        'price',
        'payment_status',
        'creation_idempotency_key',
        'hold_expires_at',
    ];


    protected function casts(): array
    {
        return [
            'booking_date'      => 'date',
            'booking_time'      => 'datetime:H:i',
            'status'            => BookingStatus::class,
            'price'             => 'decimal:2',
            'payment_status'    => PaymentStatus::class,
            'consultation_type' => ConsultationType::class,
            'hold_expires_at'   => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function doctorProfile(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id', 'user_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(AvailabilitySlot::class, 'availability_slot_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function noShowReport(): HasOne
    {
        return $this->hasOne(BookingNoShowReport::class);
    }
}
