<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilitySlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "day" => $this->day?->toDateString(),
            "start_time" => $this->start_time,
            "end_time" => $this->end_time,
            "is_booked" => $this->is_booked,
        ];
    }
}
