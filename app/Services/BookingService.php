<?php

namespace App\Services;

use App\Actions\Booking\CreateBookingAction;
use App\Models\Booking;

class BookingService
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected CreateBookingAction $createBookingAction) {}

    public function create(array $data): Booking
    {
        return ($this->createBookingAction)($data);
    }
}
