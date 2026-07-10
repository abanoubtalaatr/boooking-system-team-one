<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Collection;

final readonly class HomeData
{
    public function __construct(
        public Collection $nearbyDoctors,
        public Collection $topRatedDoctors,
        public Collection $specializations,
        public Collection $promotions,
    ) {}
}
