<?php

namespace App\Repositories\Eloquent;

use App\Models\AvailabilitySlot;
use App\Repositories\Contracts\AvailabilitySlotRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class EloquentAvailabilitySlotRepository implements AvailabilitySlotRepositoryInterface
{
    public function forDoctor(int $doctorId, Request $request): Collection
    {
        return AvailabilitySlot::query()
            ->where('doctor_id', $doctorId)
            ->when(! $request->boolean('include_booked'), fn ($query) => $query->where('is_booked', false))
            ->when($request->filled('day'), fn ($query) => $query->whereDate('day', $request->string('day')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('day', '>=', $request->string('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('day', '<=', $request->string('date_to')))
            ->where(function ($query): void {
                $query->whereDate('day', '>', now()->toDateString())
                    ->orWhere(function ($todayQuery): void {
                        $todayQuery->whereDate('day', now()->toDateString())
                            ->whereTime('start_time', '>=', now()->toTimeString());
                    });
            })
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();
    }

    public function findById(int $id): ?AvailabilitySlot
    {
        return AvailabilitySlot::query()->find($id);
    }
}
