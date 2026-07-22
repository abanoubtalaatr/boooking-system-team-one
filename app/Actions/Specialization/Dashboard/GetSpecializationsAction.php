<?php

declare(strict_types=1);

namespace App\Actions\Specialization\Dashboard;

use App\Models\Specialization;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetSpecializationsAction
{
    public function __invoke(): LengthAwarePaginator
    {
        return Specialization::query()
            ->when(
                request('search'),
                fn ($query) => $query->where(
                    'name',
                    'like',
                    '%' . request('search') . '%'
                )
            )
            ->withCount('doctors')
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }
}
