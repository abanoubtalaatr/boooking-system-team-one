<?php

namespace App\Http\Controllers\Api\Policy;

use App\Http\Controllers\Controller;
use App\Http\Resources\PolicyResource;
use App\Services\PolicyService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PolicyController extends Controller
{
    //
    public function __construct(
        private readonly PolicyService $policyService,
    ) {}

    // get privacy policy
    public function privacy(): AnonymousResourceCollection
    {
        return PolicyResource::collection(
            $this->policyService->privacy()
        );
    }

    // get terms and conditions
    public function terms(): AnonymousResourceCollection
    {
        return PolicyResource::collection(
            $this->policyService->terms()
        );
    }
}
