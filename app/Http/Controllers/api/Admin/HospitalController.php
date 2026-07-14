<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHospitalRequest;
use App\Http\Requests\Admin\UpdateHospitalRequest;
use App\Http\Resources\HospitalResource;
use App\Models\Hospital;
use App\Repositories\Contracts\HospitalRepositoryInterface;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class HospitalController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly HospitalRepositoryInterface $hospitals)
    {
    }

    public function index(): JsonResponse
    {
        return $this->apiResponse(HospitalResource::collection($this->hospitals->paginate()));
    }

    public function store(StoreHospitalRequest $request): JsonResponse
    {
        $hospital = $this->hospitals->create([
            ...$request->validated(),
            "admin_id" => $request->user()->id,
        ]);

        return $this->apiResponse(new HospitalResource($hospital), "Hospital created.", 201);
    }

    public function show(Hospital $hospital): JsonResponse
    {
        return $this->apiResponse(new HospitalResource($hospital));
    }

    public function update(UpdateHospitalRequest $request, Hospital $hospital): JsonResponse
    {
        $hospital = $this->hospitals->update($hospital, $request->validated());

        return $this->apiResponse(new HospitalResource($hospital));
    }

    public function destroy(Hospital $hospital): JsonResponse
    {
        $this->hospitals->delete($hospital);

        return $this->apiResponse(null, "Hospital deleted.");
    }
}
