<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Initiated = 'initiated';
    case PendingVerification = 'pending_verification';
    case CashDue = 'cash_due';
    case CashCollected = 'cash_collected';
    case Succeeded = 'succeeded';
    case Paid = 'paid';
    case Failed = 'failed';
    case RefundPending = 'refund_pending';
    case Refunded = 'refunded';
    case Voided = 'voided';
}
