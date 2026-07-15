<?php

namespace App\Http\Controllers\Api\Specialization;

use App\Http\Controllers\Controller;
use App\Http\Resources\SpecializationResource;
use App\Services\SpecializationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SpecializationController extends Controller
{
    public function __construct(protected SpecializationService $service) {}

    public function index(): AnonymousResourceCollection
    {
        return SpecializationResource::collection(
            $this->service->index()
        );
    }
}
