<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->user?->name,
            'avatar' => $this->avatar,
            'bio' => $this->bio,
            'specialty' => $this->specialty?->name,
            'hospital' => $this->hospital?->name,
            'address' => $this->hospital?->address,
            'price' => $this->price,
            'experience_years' => $this->experience_years,
            'education' => $this->education,
            'certificates' => $this->certificates,
            'language' => $this->language,
            'gender' => $this->gender,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'distance_km' => $this->when(isset($this->distance), fn () => round((float) $this->distance, 2)),
            'is_active' => $this->is_active,
            'is_favorite' => (bool) ($this->is_favorite ?? false),
            'rating' => $this->rating_avg ? round((float) $this->rating_avg, 1) : null,
            'reviews_count' => $this->reviews_count ?? 0,
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'available_slots' => AvailabilitySlotResource::collection($this->whenLoaded('availabilitySlots')),
        ];
    }
}
