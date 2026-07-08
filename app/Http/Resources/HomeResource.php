<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Data\HomeData
 */
class HomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'nearby_doctors' => DoctorResource::collection(
                $this->nearbyDoctors
            ),

            'top_rated_doctors' => DoctorResource::collection(
                $this->topRatedDoctors
            ),

            'specializations' => SpecializationResource::collection(
                $this->specializations
            ),

            'promotions' => PromotionResource::collection(
                $this->promotions
            ),
        ];
    }
}
