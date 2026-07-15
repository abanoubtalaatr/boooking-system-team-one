<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Enums\NoShowReportStatus;
use App\Models\Booking;
use App\Models\BookingNoShowReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class GetDoctorNoShowDashboardAction
{
    /**
     * @return array{
     *     eligibleBookings: LengthAwarePaginator<int, Booking>,
     *     reports: LengthAwarePaginator<int, BookingNoShowReport>,
     *     summary: array{eligible: int, pending: int, approved: int, rejected: int}
     * }
     */
    public function handle(int $doctorId): array
    {
        $eligibleQuery = Booking::query()
            ->with([
                'patient:id,name,phone',
                'slot:id,day,start_time,end_time',
                'latestPayment' => fn ($query) => $query->select([
                    'payments.id',
                    'payments.booking_id',
                    'payments.method',
                    'payments.status',
                    'payments.commission_amount_cents',
                    'payments.currency',
                ]),
            ])
            ->where('doctor_id', $doctorId)
            ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Completed])
            ->whereDoesntHave('noShowReport')
            ->whereHas('slot', fn (Builder $query) => $this->eligibleSlotQuery($query));

        $reportQuery = BookingNoShowReport::query()->where('doctor_id', $doctorId);

        return [
            'eligibleBookings' => (clone $eligibleQuery)
                ->latest('booking_date')
                ->paginate(10, pageName: 'eligible_page')
                ->withQueryString(),
            'reports' => (clone $reportQuery)
                ->with([
                    'booking:id,booking_number,patient_id,booking_date,booking_time,status,payment_status',
                    'booking.patient:id,name,phone',
                ])
                ->latest()
                ->paginate(10, pageName: 'reports_page')
                ->withQueryString(),
            'summary' => [
                'eligible' => (clone $eligibleQuery)->count(),
                'pending' => (clone $reportQuery)->where('status', NoShowReportStatus::PendingReview)->count(),
                'approved' => (clone $reportQuery)->where('status', NoShowReportStatus::Approved)->count(),
                'rejected' => (clone $reportQuery)->where('status', NoShowReportStatus::Rejected)->count(),
            ],
        ];
    }

    private function eligibleSlotQuery(Builder $query): void
    {
        $oneHourAgo = now()->subHour();

        $query->where(function (Builder $query) use ($oneHourAgo): void {
            $query->whereDate('day', '<', today());

            if (now()->gte(today()->addHour())) {
                $query->orWhere(function (Builder $query) use ($oneHourAgo): void {
                    $query->whereDate('day', today())
                        ->whereTime('end_time', '<=', $oneHourAgo->toTimeString());
                });
            }
        });
    }
}
