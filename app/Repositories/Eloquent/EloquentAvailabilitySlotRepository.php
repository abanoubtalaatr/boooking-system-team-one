<?php

namespace App\Repositories\Eloquent;

use App\Models\AvailabilitySlot;
use App\Repositories\Contracts\AvailabilitySlotRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentAvailabilitySlotRepository implements AvailabilitySlotRepositoryInterface
{
    public function find(string $id): ?AvailabilitySlot
    {
        return AvailabilitySlot::find($id);
    }

    public function create(array $data): AvailabilitySlot
    {
        return AvailabilitySlot::create($data);
    }

    public function update(AvailabilitySlot $slot, array $data): AvailabilitySlot
    {
        $slot->update($data);

        return $slot->refresh();
    }

    public function delete(AvailabilitySlot $slot): bool
    {
        return (bool) $slot->delete();
    }

    public function paginate(string $doctorId, int $perPage = 15): LengthAwarePaginator
    {
        return AvailabilitySlot::where("doctor_id", $doctorId)
            ->orderBy("day")
            ->orderBy("start_time")
            ->paginate($perPage);
    }

    public function findAvailableSlotsForDoctor(string $doctorId, string $day): Collection
    {
        return AvailabilitySlot::where("doctor_id", $doctorId)
            ->where("day", $day)
            ->orderBy("start_time")
            ->get();
    }
}
