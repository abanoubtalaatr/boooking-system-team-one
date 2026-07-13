<?php

namespace App\Services;

use App\Models\AvailabilitySlot;
use App\Repositories\Contracts\AvailabilitySlotRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AvailabilitySlotService
{
    public function __construct(
        protected AvailabilitySlotRepositoryInterface $slots
    ) {}

    public function listForDoctor(int $doctorId, Request $request): Collection
    {
        return $this->slots->forDoctor($doctorId, $request);
    }

    public function show(int $id): AvailabilitySlot
    {
        $slot = $this->slots->findById($id);

        if (!$slot) {
            throw new NotFoundHttpException('Slot not found');
        }

        return $slot;
    }
}