<?php

namespace App\Jobs;

use App\Enums\BookingStatus;
use App\Enums\SlotReservationStatus;
use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExpireBookingHolds implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 55;

    public int $tries = 3;

    public int $timeout = 45;

    public array $backoff = [1, 5, 15];

    public function handle(): void
    {
        Booking::query()
            ->where('status', BookingStatus::PendingPayment)
            ->where('hold_expires_at', '<=', now())
            ->select('id')
            ->chunkById(100, function ($bookings): void {
                foreach ($bookings as $booking) {
                    $this->expire((int) $booking->id);
                }
            });
    }

    private function expire(int $bookingId): void
    {
        DB::transaction(function () use ($bookingId): void {
            $booking = Booking::query()->lockForUpdate()->find($bookingId);

            if (! $booking || $booking->status !== BookingStatus::PendingPayment || $booking->hold_expires_at?->isFuture()) {
                return;
            }

            $booking->update(['status' => BookingStatus::Expired]);
            $booking->slot()
                ->where('reserved_booking_id', $booking->id)
                ->where('reservation_status', SlotReservationStatus::Held)
                ->update([
                    'is_booked' => false,
                    'reservation_status' => SlotReservationStatus::Available,
                    'reserved_booking_id' => null,
                    'reserved_until' => null,
                ]);
        }, attempts: 3);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Booking hold expiry job failed.', [
            'error' => $exception?->getMessage(),
        ]);
    }
}
