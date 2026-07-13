<?php

namespace App\Services;

use App\Actions\Booking\CancelBookingAction;
use App\Actions\Booking\CreateBookingAction;
use App\Models\Booking;

class BookingService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected CreateBookingAction $createBookingAction,
        protected CancelBookingAction $cancelBookingAction,
    ) {}

    public function create(array $data): Booking
    {
        return ($this->createBookingAction)($data);
    }

    public function cancel(Booking $booking): Booking
    {
        return ($this->cancelBookingAction)($booking);
    }
}
