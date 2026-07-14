<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilitySlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'doctor_id'  => $this->doctor_id,
            'day'        => $this->day->toDateString(),
            'start_time' => substr($this->start_time, 0, 5),
            'end_time'   => substr($this->end_time, 0, 5),
            'is_booked'  => $this->is_booked,
        ];
    }
}