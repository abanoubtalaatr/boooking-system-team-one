<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSpecialtyRequest;
use App\Http\Requests\Admin\UpdateSpecialtyRequest;
use App\Http\Resources\SpecialtyResource;
use App\Models\Specialty;
use App\Repositories\Contracts\SpecialtyRepositoryInterface;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class SpecialtyController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly SpecialtyRepositoryInterface $specialties)
    {
    }

    public function index(): JsonResponse
    {
        return $this->apiResponse(SpecialtyResource::collection($this->specialties->paginate()));
    }

    public function store(StoreSpecialtyRequest $request): JsonResponse
    {
        $specialty = $this->specialties->create([
            ...$request->validated(),
            "admin_id" => $request->user()->id,
        ]);

        return $this->apiResponse(new SpecialtyResource($specialty), "Specialty created.", 201);
    }

    public function show(Specialty $specialty): JsonResponse
    {
        return $this->apiResponse(new SpecialtyResource($specialty));
    }

    public function update(UpdateSpecialtyRequest $request, Specialty $specialty): JsonResponse
    {
        $specialty = $this->specialties->update($specialty, $request->validated());

        return $this->apiResponse(new SpecialtyResource($specialty));
    }

    public function destroy(Specialty $specialty): JsonResponse
    {
        $this->specialties->delete($specialty);

        return $this->apiResponse(null, "Specialty deleted.");
    }
}
