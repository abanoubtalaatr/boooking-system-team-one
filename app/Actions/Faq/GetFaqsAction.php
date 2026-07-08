<?php

declare(strict_types=1);

namespace App\Actions\Faq;

use App\Models\Faq;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetFaqsAction
{
    public function __invoke(?string $search, ?int $category, int $perPage = 10,): LengthAwarePaginator {

        return Faq::query()

            ->with('category')
            ->when(
                $search,
                fn ($query) => $query->where(
                    'question',
                    'like',
                    "%{$search}%"
                )
            )
            ->when(
                $category,
                fn ($query) => $query->where(
                    'faq_category_id',
                    $category
                )
            )
            ->latest()
            ->paginate($perPage);
    }
}
