<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentRefund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaymentRefundedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public Payment $payment,
        public PaymentRefund $refund,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'payment_id' => $this->payment->uuid,
            'refund_id' => $this->refund->uuid,
            'status' => 'refunded',
            'message' => 'تم استرداد قيمة الحجز كاملة.',
        ];
    }
}
