<?php

namespace App\Services;

use App\Actions\Booking\CancelBookingAction;
use App\Actions\Booking\CreateBookingAction;
use App\Actions\Booking\GetBookingsAction;
use App\Actions\Booking\RescheduleBookingAction;
use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        protected CreateBookingAction $createBookingAction,
        protected CancelBookingAction $cancelBookingAction,
        protected GetBookingsAction $getBookingsAction,
        protected RescheduleBookingAction $rescheduleBookingAction,
    ) {}

    public function listForPatient(int $patientId, ?string $status = null): LengthAwarePaginator
    {
        return ($this->getBookingsAction)($patientId, $status);
    }

    public function create(array $data, int $patientId): Booking
    {
        return ($this->createBookingAction)($data, $patientId);
    }

    public function showForPatient(Booking $booking, int $patientId): Booking
    {
        if ((int) $booking->patient_id !== $patientId) {
            throw ValidationException::withMessages([
                'booking' => 'Booking not found.',
            ]);
        }

        return $booking->load(['doctor.doctorProfile.specialty', 'slot']);
    }

    public function cancel(Booking $booking, int $patientId): Booking
    {
        if ((int) $booking->patient_id !== $patientId) {
            throw ValidationException::withMessages([
                'booking' => 'Booking not found.',
            ]);
        }

        return ($this->cancelBookingAction)($booking);
    }

    public function reschedule(Booking $booking, int $patientId, array $data): Booking {

        return ($this->rescheduleBookingAction)(
            $booking,
            $patientId,
            $data
        );
    }
}
