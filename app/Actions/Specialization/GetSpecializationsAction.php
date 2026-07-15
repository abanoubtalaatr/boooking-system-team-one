<?php

declare(strict_types=1);

namespace App\Actions\Specialization;

use App\Models\Specialization;
use Illuminate\Database\Eloquent\Collection;

class GetSpecializationsAction
{
    public function __invoke(): Collection
    {
        return Specialization::query()
            ->orderBy('name')
            ->get();
    }
}
