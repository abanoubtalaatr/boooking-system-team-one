<?php

namespace App\Actions\Booking;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetBookingsAction
{
    public function __invoke(int $patientId, ?string $status = null): LengthAwarePaginator
    {
        return Booking::query()
            ->with(['doctor.doctorProfile.specialty', 'slot'])
            ->where('patient_id', $patientId)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15);
    }
}
