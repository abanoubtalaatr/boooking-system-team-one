<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Faq\GetFaqsAction;
use App\Http\Requests\Faq\FaqIndexRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FaqService
{
    public function __construct(private readonly GetFaqsAction $getFaqs,) {}

    public function index(FaqIndexRequest $request,): LengthAwarePaginator {
        return ($this->getFaqs)(
            search: $request->validated('search'),
            category: $request->integer('category'),
            perPage: (int) ($request->validated('per_page') ?? 10),
        );
    }
}
