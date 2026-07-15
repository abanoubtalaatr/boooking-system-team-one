<?php

namespace App\Actions\Payment;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListDashboardPaymentsAction
{
    /**
     * @param  array{
     *     status?: string|null,
     *     method?: string|null,
     *     doctor_id?: int|null,
     *     patient_id?: int|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int|null
     * }  $filters
     * @return LengthAwarePaginator<int, Payment>
     */
    public function handle(array $filters, ?int $forcedDoctorId = null): LengthAwarePaginator
    {
        return Payment::query()
            ->with([
                'doctor:id,name,email',
                'patient:id,name,email,phone',
                'booking:id,booking_number,booking_date,booking_time,consultation_type,status,payment_status',
            ])
            ->when(
                $forcedDoctorId !== null,
                fn ($query) => $query->where('doctor_id', $forcedDoctorId),
                fn ($query) => $query->when(
                    isset($filters['doctor_id']),
                    fn ($query) => $query->where('doctor_id', $filters['doctor_id'])
                )
            )
            ->when(
                isset($filters['patient_id']),
                fn ($query) => $query->where('patient_id', $filters['patient_id'])
            )
            ->when(
                isset($filters['status']),
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->when(
                isset($filters['method']),
                fn ($query) => $query->where('method', $filters['method'])
            )
            ->when(
                isset($filters['date_from']),
                fn ($query) => $query->whereDate('created_at', '>=', $filters['date_from'])
            )
            ->when(
                isset($filters['date_to']),
                fn ($query) => $query->whereDate('created_at', '<=', $filters['date_to'])
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();
    }
}
