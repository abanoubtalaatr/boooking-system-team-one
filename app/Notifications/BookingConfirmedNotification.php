<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Referenced by AcceptBookingAction as `BookingConfirmed` in the prompt;
 * named ...Notification here to avoid clashing with a Booking-module Event of the same name.
 */
class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ["database"];
    }

    public function toArray(object $notifiable): array
    {
        return [
            "booking_id" => $this->booking->id,
            "status" => "confirmed",
        ];
    }
}
