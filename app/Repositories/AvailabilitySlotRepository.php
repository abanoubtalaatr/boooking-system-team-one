<?php

namespace App\Repositories;

use App\Models\AvailabilitySlot;
use App\Repositories\Contracts\AvailabilitySlotRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class AvailabilitySlotRepository implements AvailabilitySlotRepositoryInterface
{
    public function forDoctor(int $doctorId, Request $request): Collection
    {
        $query = AvailabilitySlot::query()->where('doctor_id', $doctorId);

        if (!$request->boolean('include_booked')) {$query->where('is_booked', false);}
        if ($request->filled('day')) { $query->whereDate('day', $request->input('day'));}
        if ($request->filled('date_from')) {$query->whereDate('day', '>=', $request->input('date_from'));}
        if ($request->filled('date_to')) {  $query->whereDate('day', '<=', $request->input('date_to'));}

        $query->where(function ($q) {
            $q->whereDate('day', '>', now()->toDateString())
              ->orWhere(function ($q2) {
                  $q2->whereDate('day', now()->toDateString())
                     ->whereTime('start_time', '>=', now()->toTimeString());
              });
        });

        return $query->orderBy('day')->orderBy('start_time')->get();
    }

    public function findById(int $id): ?AvailabilitySlot
    {
        return AvailabilitySlot::find($id);
    }
}