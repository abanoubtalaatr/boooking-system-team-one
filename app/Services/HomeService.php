<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Home\GetNearbyDoctorsAction;
use App\Actions\Home\GetPromotionsAction;
use App\Actions\Home\GetSpecializationsAction;
use App\Actions\Home\GetTopRatedDoctorsAction;
use App\Data\HomeData;
use App\Http\Requests\Home\HomeIndexRequest;

class HomeService
{
    public function __construct(
        private readonly GetNearbyDoctorsAction $nearbyDoctors,
        private readonly GetTopRatedDoctorsAction $topRatedDoctors,
        private readonly GetSpecializationsAction $specializations,
        private readonly GetPromotionsAction $promotions,
    ) {}

    public function index(HomeIndexRequest $request): HomeData
    {
        $latitude = (float) ($request->validated('latitude') ?? 30.0444);
        $longitude = (float) ($request->validated('longitude') ?? 31.2357);
        $radius = (int) ($request->validated('radius') ?? 20);

        return new HomeData(
            nearbyDoctors: ($this->nearbyDoctors)(
                latitude: $latitude,
                longitude: $longitude,
                radius: $radius,
            ),

            topRatedDoctors: ($this->topRatedDoctors)(),

            specializations: ($this->specializations)(),

            promotions: ($this->promotions)(),
        );
    }
}
