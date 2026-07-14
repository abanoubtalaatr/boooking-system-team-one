<?php

namespace App\Actions\Doctor;

use App\Models\Booking;
use App\Notifications\BookingRejectedNotification;

class RejectBookingAction
{
    public function handle(Booking $booking): Booking
    {
        $booking->update(["status" => "rejected"]);

        $booking->patient->notify(new BookingRejectedNotification($booking));

        return $booking->refresh();
    }
}
