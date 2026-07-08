<?php

declare(strict_types=1);

namespace App\Actions\Home;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Collection;

class GetPromotionsAction
{
    public function __invoke(): Collection
    {
        return Promotion::query()
            ->where('is_active', true)
            ->latest()
            ->get();
    }
}
