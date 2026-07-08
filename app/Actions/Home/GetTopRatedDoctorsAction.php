<?php

declare(strict_types=1);

namespace App\Actions\Home;

use App\Models\DoctorProfile;
use Illuminate\Database\Eloquent\Collection;

class GetTopRatedDoctorsAction
{
    public function __invoke(): Collection
    {
        return DoctorProfile::query()
            ->with([
                'user',
                'specialization',
            ])
            ->orderByDesc('rating')
            ->limit(10)
            ->get();
    }
}
