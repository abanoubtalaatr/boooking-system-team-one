<?php

namespace App\Actions\Booking;

use App\Enums\NoShowReportStatus;
use App\Models\BookingNoShowReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetAdminNoShowDashboardAction
{
    /**
     * @param  array{status?: string|null, doctor_id?: int|null, per_page?: int|null}  $filters
     * @return array{
     *     reports: LengthAwarePaginator<int, BookingNoShowReport>,
     *     summary: array{total: int, pending: int, approved: int, rejected: int}
     * }
     */
    public function handle(array $filters): array
    {
        $reports = BookingNoShowReport::query()
            ->with([
                'doctor:id,name,email',
                'reviewer:id,name',
                'booking:id,booking_number,patient_id,booking_date,booking_time,status,payment_status',
                'booking.patient:id,name,phone',
                'booking.latestPayment' => fn ($query) => $query->select([
                    'payments.id',
                    'payments.booking_id',
                    'payments.method',
                    'payments.status',
                    'payments.commission_amount_cents',
                    'payments.currency',
                ]),
            ])
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['doctor_id']), fn ($query) => $query->where('doctor_id', $filters['doctor_id']))
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();

        return [
            'reports' => $reports,
            'summary' => [
                'total' => BookingNoShowReport::query()->count(),
                'pending' => BookingNoShowReport::query()->where('status', NoShowReportStatus::PendingReview)->count(),
                'approved' => BookingNoShowReport::query()->where('status', NoShowReportStatus::Approved)->count(),
                'rejected' => BookingNoShowReport::query()->where('status', NoShowReportStatus::Rejected)->count(),
            ],
        ];
    }
}
