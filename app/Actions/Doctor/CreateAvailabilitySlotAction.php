<?php

namespace App\Actions\Doctor;

use App\Models\AvailabilitySlot;
use App\Models\DoctorProfile;
use App\Repositories\Contracts\AvailabilitySlotRepositoryInterface;
use Illuminate\Validation\ValidationException;

class CreateAvailabilitySlotAction
{
    public function __construct(
        private readonly AvailabilitySlotRepositoryInterface $slots,
    ) {
    }

    public function handle(DoctorProfile $doctor, array $data): AvailabilitySlot
    {
        $existing = $this->slots->findAvailableSlotsForDoctor($doctor->id, $data["day"]);

        $overlaps = $existing->contains(function (AvailabilitySlot $slot) use ($data) {
            return $data["start_time"] < $slot->end_time && $data["end_time"] > $slot->start_time;
        });

        if ($overlaps) {
            throw ValidationException::withMessages([
                "start_time" => "This slot overlaps with an existing availability slot.",
            ]);
        }

        return $this->slots->create([
            "doctor_id" => $doctor->id,
            "day" => $data["day"],
            "start_time" => $data["start_time"],
            "end_time" => $data["end_time"],
        ]);
    }
}
