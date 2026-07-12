<?php

namespace App\Actions\Doctor;

use App\Models\Booking;
use App\Notifications\BookingConfirmedNotification;

class AcceptBookingAction
{
    /**
     * Booking model/lifecycle owned by the (separate) Booking module.
     * This action only performs the doctor-side status transition + notification.
     */
    public function handle(Booking $booking): Booking
    {
        $booking->update(["status" => "confirmed"]);

        $booking->patient->notify(new BookingConfirmedNotification($booking));

        return $booking->refresh();
    }
}
