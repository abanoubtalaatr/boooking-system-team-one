<?php

namespace App\Services\Web;

use App\Models\AvailabilitySlot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AvailabilitySlotService
{
    /**
     * Split a day/time range into consecutive one-hour slots for a doctor.
     *
     * @return array{created: bool, conflict: ?string} 'conflict' holds an
     *         Arabic message for the first overlapping slot found, or null
     *         if the whole range was free and the slots were created.
     */
    public function createHourlySlots(int $doctorId, string $day, string $startTime, string $endTime): array
    {
        // Two passes on purpose: first check the *entire* range for
        // conflicts, then only create if none exist. Doing the check and
        // the create in a single pass would let earlier hours get saved
        // before a later hour is found to conflict, leaving half the
        // request committed with no success message shown to the user.
        $conflict = $this->firstConflict($doctorId, $day, $startTime, $endTime);

        if ($conflict) {
            return ['created' => false, 'conflict' => $conflict];
        }

        DB::transaction(function () use ($doctorId, $day, $startTime, $endTime) {
            $cursor = Carbon::parse($day . ' ' . $startTime);
            $end    = Carbon::parse($day . ' ' . $endTime);

            while ($cursor->lessThan($end)) {
                $next = $cursor->copy()->addHour();

                AvailabilitySlot::create([
                    'doctor_id'  => $doctorId,
                    'day'        => $day,
                    'start_time' => $cursor->format('H:i:s'),
                    'end_time'   => $next->format('H:i:s'),
                ]);

                $cursor = $next;
            }
        });

        return ['created' => true, 'conflict' => null];
    }

    /**
     * Look for the first hour in the range that already has a slot.
     * Returns an Arabic error message ready to show the doctor, or null.
     */
    protected function firstConflict(int $doctorId, string $day, string $startTime, string $endTime): ?string
    {
        $cursor = Carbon::parse($day . ' ' . $startTime);
        $end    = Carbon::parse($day . ' ' . $endTime);

        while ($cursor->lessThan($end)) {
            if ($this->slotExists($doctorId, $day, $cursor->format('H:i:s'))) {
                return 'يوجد بالفعل موعد يبدأ الساعة ' . $cursor->format('h:i A') . '.';
            }

            $cursor->addHour();
        }

        return null;
    }

    /**
     * Whether the doctor already has a slot starting at this day/time.
     */
    public function slotExists(int $doctorId, string $day, string $startTime, ?int $excludingId = null): bool
    {
        return AvailabilitySlot::where('doctor_id', $doctorId)
            ->whereDate('day', $day)
            ->whereTime('start_time', $startTime)
            ->when($excludingId, fn ($query) => $query->where('id', '!=', $excludingId))
            ->exists();
    }
}