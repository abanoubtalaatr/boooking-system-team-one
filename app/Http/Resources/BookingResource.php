<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'booking_date' => $this->booking_date?->toDateString(),
            'booking_time' => $this->booking_time?->format('H:i'),
            'consultation_type' => $this->consultation_type,
            'price' => $this->price,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'hold_expires_at' => $this->hold_expires_at,
            'doctor' => [
                'id' => $this->doctor?->id,
                'name' => $this->doctor?->name,
                'specialty' => $this->doctor?->doctorProfile?->specialty?->name,
            ],
            'slot' => [
                'id' => $this->slot?->id,
                'day' => $this->slot?->day?->toDateString(),
                'start_time' => $this->slot?->start_time,
                'end_time' => $this->slot?->end_time,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
