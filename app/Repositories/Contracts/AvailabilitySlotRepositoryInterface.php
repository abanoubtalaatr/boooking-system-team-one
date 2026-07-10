<?php

namespace App\Repositories\Contracts;

use App\Models\AvailabilitySlot;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface AvailabilitySlotRepositoryInterface
{
    public function forDoctor(int $doctorId, Request $request): Collection;

    public function findById(int $id): ?AvailabilitySlot;
}