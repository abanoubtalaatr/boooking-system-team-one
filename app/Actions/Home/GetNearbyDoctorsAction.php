<?php

declare(strict_types=1);

namespace App\Actions\Home;

use App\Models\DoctorProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GetNearbyDoctorsAction
{
    /**
     * Get nearby doctors ordered by distance.
     */
    public function __invoke(float $latitude, float $longitude, int $radius = 20, int $limit = 10,): Collection {

        return DoctorProfile::query()
            ->select('*')
            ->selectRaw(
                '(6371 * acos(
                    cos(radians(?))
                    * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?))
                    + sin(radians(?))
                    * sin(radians(latitude))
                )) AS distance',
                [
                    $latitude,
                    $longitude,
                    $latitude,
                ]
            )
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with([
                'user',
                'specialization',
            ])
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->limit($limit)
            ->get();
    }
}
