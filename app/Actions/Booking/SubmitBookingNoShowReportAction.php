<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Enums\NoShowReportStatus;
use App\Exceptions\NoShowReportDomainException;
use App\Models\Booking;
use App\Models\BookingNoShowReport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubmitBookingNoShowReportAction
{
    public function handle(Booking $booking, int $doctorId, string $reason): BookingNoShowReport
    {
        return DB::transaction(function () use ($booking, $doctorId, $reason): BookingNoShowReport {
            $lockedBooking = Booking::query()->with('slot')->lockForUpdate()->findOrFail($booking->id);

            if ((int) $lockedBooking->doctor_id !== $doctorId) {
                throw new NoShowReportDomainException('غير مسموح بتقديم بلاغ لهذا الحجز.', 'forbidden', 403);
            }

            if (! in_array($lockedBooking->status, [BookingStatus::Confirmed, BookingStatus::Completed], true)) {
                throw new NoShowReportDomainException('لا يمكن تقديم بلاغ عدم حضور في حالة الحجز الحالية.', 'booking_not_eligible', 409);
            }

            if (! $lockedBooking->slot) {
                throw new NoShowReportDomainException('موعد الحجز غير متاح للمراجعة.', 'booking_slot_missing', 409);
            }

            $eligibleAt = Carbon::parse(
                $lockedBooking->slot->day->toDateString().' '.$lockedBooking->slot->end_time,
            )->addHour();

            if (now()->lt($eligibleAt)) {
                throw new NoShowReportDomainException('يمكن تقديم البلاغ بعد انتهاء الموعد بساعة.', 'report_too_early', 409);
            }

            if (BookingNoShowReport::query()->where('booking_id', $lockedBooking->id)->exists()) {
                throw new NoShowReportDomainException('تم تقديم بلاغ لهذا الحجز من قبل.', 'report_already_exists', 409);
            }

            return BookingNoShowReport::query()->create([
                'booking_id' => $lockedBooking->id,
                'doctor_id' => $doctorId,
                'status' => NoShowReportStatus::PendingReview,
                'reason' => $reason,
            ]);
        }, attempts: 3);
    }
}
