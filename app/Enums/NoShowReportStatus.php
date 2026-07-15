<?php

namespace App\Enums;

enum NoShowReportStatus: string
{
    case PendingReview = 'pending_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
