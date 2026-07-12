<?php

namespace App\Repositories\Contracts;

use App\Models\AvailabilitySlot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AvailabilitySlotRepositoryInterface
{
    public function find(string $id): ?AvailabilitySlot;

    public function create(array $data): AvailabilitySlot;

    public function update(AvailabilitySlot $slot, array $data): AvailabilitySlot;

    public function delete(AvailabilitySlot $slot): bool;

    public function paginate(string $doctorId, int $perPage = 15): LengthAwarePaginator;

    /** Slots for a doctor on a given day, used to check overlaps and list availability. */
    public function findAvailableSlotsForDoctor(string $doctorId, string $day): Collection;
}
