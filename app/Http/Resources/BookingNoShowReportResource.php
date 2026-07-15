<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingNoShowReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'booking_number' => $this->whenLoaded('booking', fn () => $this->booking->booking_number),
            'doctor_id' => $this->doctor_id,
            'status' => $this->status,
            'reason' => $this->reason,
            'reviewed_by' => $this->reviewed_by,
            'review_note' => $this->review_note,
            'reviewed_at' => $this->reviewed_at,
            'created_at' => $this->created_at,
        ];
    }
}
