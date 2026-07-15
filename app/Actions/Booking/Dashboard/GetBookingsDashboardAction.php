<?php

namespace App\Actions\Booking\Dashboard;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetBookingsDashboardAction
{
    public function __invoke(array $filters): LengthAwarePaginator
    {
        return Booking::query()
            ->with([
                'patient',
                'doctor',
                'slot',
            ])
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $query->where('booking_number', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('doctor', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                }
            )
            ->when(
                $filters['status'] ?? null,
                fn($q, $status) => $q->where('status', $status)
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }
}
