<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case PendingPayment = 'pending_payment';
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Rescheduled = 'rescheduled';
    case Expired = 'expired';
    case PaymentFailed = 'payment_failed';
    case RefundPending = 'refund_pending';
    case Refunded = 'refunded';
}
