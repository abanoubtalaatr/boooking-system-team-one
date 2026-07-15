<?php

namespace App\Enums;

enum WalletWithdrawalStatus: string
{
    case PendingReview = 'pending_review';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
