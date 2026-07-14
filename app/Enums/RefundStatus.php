<?php

namespace App\Enums;

enum RefundStatus: string
{
    case Pending = 'pending';
    case PendingVerification = 'pending_verification';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
}
