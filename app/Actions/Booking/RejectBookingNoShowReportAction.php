<?php

namespace App\Actions\Booking;

use App\Enums\NoShowReportStatus;
use App\Exceptions\NoShowReportDomainException;
use App\Models\BookingNoShowReport;
use App\Models\User;
use App\Notifications\BookingNoShowReportReviewedNotification;
use Illuminate\Support\Facades\DB;

class RejectBookingNoShowReportAction
{
    public function handle(BookingNoShowReport $report, User $admin, ?string $note): BookingNoShowReport
    {
        return DB::transaction(function () use ($report, $admin, $note): BookingNoShowReport {
            $lockedReport = BookingNoShowReport::query()->lockForUpdate()->findOrFail($report->id);

            if ($lockedReport->status !== NoShowReportStatus::PendingReview) {
                throw new NoShowReportDomainException('تمت مراجعة هذا البلاغ من قبل.', 'report_already_reviewed', 409);
            }

            $lockedReport->update([
                'status' => NoShowReportStatus::Rejected,
                'reviewed_by' => $admin->id,
                'review_note' => $note,
                'reviewed_at' => now(),
            ]);
            $lockedReport->doctor->notify(
                (new BookingNoShowReportReviewedNotification($lockedReport))->afterCommit(),
            );

            return $lockedReport->fresh(['booking', 'doctor', 'reviewer']);
        }, attempts: 3);
    }
}
