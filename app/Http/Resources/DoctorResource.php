<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->user->name,
            "email" => $this->user->email,
            "status" => $this->user->status,
            "bio" => $this->bio,
            "consultation_price" => $this->consultation_price,
            "is_approved" => $this->is_approved,
            "specialties" => SpecialtyResource::collection($this->whenLoaded("specialties")),
            "hospitals" => HospitalResource::collection($this->whenLoaded("hospitals")),
            // Assumption: Rating module exposes DoctorProfile::averageRating(); see NOTES.md.
            "average_rating" => $this->when(
                method_exists($this->resource, "averageRating"),
                fn () => $this->averageRating()
            ),
        ];
    }
}
