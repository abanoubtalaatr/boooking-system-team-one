<?php

namespace App\Notifications;

use App\Enums\NoShowReportStatus;
use App\Models\BookingNoShowReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingNoShowReportReviewedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BookingNoShowReport $report) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $approved = $this->report->status === NoShowReportStatus::Approved;

        return [
            'report_id' => $this->report->id,
            'booking_id' => $this->report->booking_id,
            'status' => $this->report->status->value,
            'message' => $approved
                ? 'تم قبول بلاغ عدم الحضور وتسوية الحجز ماليًا.'
                : 'تم رفض بلاغ عدم الحضور. يرجى التواصل مع الدعم عند الحاجة.',
        ];
    }
}
