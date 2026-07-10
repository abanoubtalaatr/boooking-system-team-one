<?php

namespace App\Http\Controllers\Api\Faq;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faq\FaqIndexRequest;
use App\Http\Resources\FaqResource;
use App\Services\FaqService;

class FaqController extends Controller
{
    public function __construct(private readonly FaqService $faqService,) {}

    public function index(FaqIndexRequest $request)
    {
        return FaqResource::collection(
            $this->faqService->index($request)
        );
    }
}
