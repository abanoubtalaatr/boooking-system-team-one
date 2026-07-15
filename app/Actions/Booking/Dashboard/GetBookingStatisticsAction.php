<?php

namespace App\Actions\Booking\Dashboard;

use App\Enums\BookingStatus;
use App\Models\Booking;

class GetBookingStatisticsAction
{
    public function __invoke(): array
    {
        $counts = Booking::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => Booking::count(),
            'pending' => $counts[BookingStatus::Pending->value] ?? 0,
            'confirmed' => $counts[BookingStatus::Confirmed->value] ?? 0,
            'completed' => $counts[BookingStatus::Completed->value] ?? 0,
            'cancelled' => $counts[BookingStatus::Cancelled->value] ?? 0,
        ];
    }
}
