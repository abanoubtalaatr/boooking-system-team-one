<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "bio" => $this->bio,
            "consultation_price" => $this->consultation_price,
            "is_approved" => $this->is_approved,
        ];
    }
}
