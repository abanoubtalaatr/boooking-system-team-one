<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\SlotReservationStatus;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\User;
use App\Services\Payments\MoneyCalculator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateBookingAction
{
    public function __construct(private readonly MoneyCalculator $money) {}

    public function __invoke(array $data, int $patientId): Booking
    {
        $existingBooking = Booking::query()
            ->where('patient_id', $patientId)
            ->where('creation_idempotency_key', $data['idempotency_key'])
            ->first();

        if ($existingBooking) {
            return $this->loadBooking($existingBooking);
        }

        return DB::transaction(function () use ($data, $patientId): Booking {
            $slot = AvailabilitySlot::query()
                ->whereKey($data['availability_slot_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $concurrentBooking = Booking::query()
                ->where('patient_id', $patientId)
                ->where('creation_idempotency_key', $data['idempotency_key'])
                ->first();

            if ($concurrentBooking) {
                return $this->loadBooking($concurrentBooking);
            }

            $this->releaseExpiredHold($slot);

            if ((int) $slot->doctor_id !== (int) $data['doctor_id']) {
                throw ValidationException::withMessages(['availability_slot_id' => 'Invalid availability slot.']);
            }

            if ($slot->is_booked || $slot->reservation_status !== SlotReservationStatus::Available) {
                throw ValidationException::withMessages(['availability_slot_id' => 'This slot is already booked or held.']);
            }

            $slotStartsAt = Carbon::parse($slot->day->toDateString().' '.$slot->start_time);

            if (! $slotStartsAt->isFuture()) {
                throw ValidationException::withMessages(['availability_slot_id' => 'The availability slot must be in the future.']);
            }

            $doctor = User::query()->with('doctorProfile')->findOrFail($data['doctor_id']);

            if (! $doctor->doctorProfile || ! $doctor->isDoctor()) {
                throw ValidationException::withMessages(['doctor_id' => 'Doctor profile not found.']);
            }

            if (! $doctor->doctorProfile->is_active) {
                throw ValidationException::withMessages(['doctor_id' => 'Doctor is not active.']);
            }

            if ($doctor->doctorProfile->price === null || $this->money->decimalToCents((string) $doctor->doctorProfile->price) < 1) {
                throw ValidationException::withMessages(['doctor_id' => 'Doctor booking price is not configured.']);
            }

            $holdExpiresAt = now()->addMinutes((int) config('payments.booking_hold_minutes', 15));
            $booking = Booking::query()->create([
                'booking_number' => $this->generateBookingNumber(),
                'patient_id' => $patientId,
                'doctor_id' => $data['doctor_id'],
                'availability_slot_id' => $slot->id,
                'booking_date' => $slot->day,
                'booking_time' => $slot->start_time,
                'consultation_type' => $data['consultation_type'],
                'price' => $doctor->doctorProfile->price,
                'status' => BookingStatus::PendingPayment,
                'payment_status' => PaymentStatus::Pending,
                'creation_idempotency_key' => $data['idempotency_key'],
                'hold_expires_at' => $holdExpiresAt,
            ]);

            $slot->update([
                'is_booked' => true,
                'reservation_status' => SlotReservationStatus::Held,
                'reserved_booking_id' => $booking->id,
                'reserved_until' => $holdExpiresAt,
            ]);

            return $this->loadBooking($booking);
        }, attempts: 3);
    }

    private function releaseExpiredHold(AvailabilitySlot $slot): void
    {
        if ($slot->reservation_status !== SlotReservationStatus::Held || ! $slot->reserved_until?->isPast()) {
            return;
        }

        Booking::query()
            ->whereKey($slot->reserved_booking_id)
            ->where('status', BookingStatus::PendingPayment)
            ->update(['status' => BookingStatus::Expired]);

        $slot->update([
            'is_booked' => false,
            'reservation_status' => SlotReservationStatus::Available,
            'reserved_booking_id' => null,
            'reserved_until' => null,
        ]);
        $slot->refresh();
    }

    private function loadBooking(Booking $booking): Booking
    {
        return $booking->load(['doctor.doctorProfile.specialty', 'slot']);
    }

    private function generateBookingNumber(): string
    {
        return 'BK-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }
}
