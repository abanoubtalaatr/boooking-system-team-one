<?php

namespace App\Jobs;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Notifications\BookingCompletedNotification;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CompletePendingBookings implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 3600;

    public int $tries = 3;

    public int $timeout = 120;

    public array $backoff = [5, 30, 120];

    public function handle(): void
    {
        Booking::query()
            ->where('status', BookingStatus::Pending)
            ->where('booking_date', '<', today()->addDay()->toDateString())
            ->whereHas('slot', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->whereDate('day', '<', today())
                        ->orWhere(function (Builder $query): void {
                            $query->whereDate('day', today())
                                ->whereTime('end_time', '<=', now()->toTimeString());
                        });
                });
            })
            ->select('id')
            ->chunkById(100, function ($bookings): void {
                foreach ($bookings as $booking) {
                    $this->complete((int) $booking->id);
                }
            });
    }

    private function complete(int $bookingId): void
    {
        DB::transaction(function () use ($bookingId): void {
            $booking = Booking::query()
                ->with(['doctor', 'slot'])
                ->lockForUpdate()
                ->find($bookingId);

            if (! $booking || $booking->status !== BookingStatus::Pending || ! $booking->slot) {
                return;
            }

            $slotEndsAt = Carbon::parse(
                $booking->slot->day->toDateString().' '.$booking->slot->end_time,
            );

            if ($slotEndsAt->isFuture()) {
                return;
            }

            $booking->update(['status' => BookingStatus::Completed]);
            $booking->doctor->notify(new BookingCompletedNotification($booking));
        }, attempts: 3);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Pending booking completion job failed.', [
            'error' => $exception?->getMessage(),
        ]);
    }
}
