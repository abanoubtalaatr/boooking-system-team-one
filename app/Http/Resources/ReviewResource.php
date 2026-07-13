<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'rating'        => $this->rating,
            'comment'       => $this->comment,
            'patient_name'  => $this->patient?->name,
            'patient_photo' => $this->patient?->profile_photo,
            'created_at'    => $this->created_at?->diffForHumans(),
        ];
    }
}