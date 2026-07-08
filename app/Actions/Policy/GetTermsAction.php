<?php

declare(strict_types=1);

namespace App\Actions\Policy;

use App\Models\Policy;
use Illuminate\Database\Eloquent\Collection;

class GetTermsAction
{
    public function __invoke(): Collection
    {
        return Policy::query()
            ->where('type', 'terms')
            ->where('is_active', true)
            ->latest()
            ->get();
    }
}
