<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\AssignSpecialtiesRequest;
use App\Http\Resources\DoctorProfileResource;
use App\Repositories\Contracts\DoctorProfileRepositoryInterface;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class AssignSpecialtiesController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DoctorService $doctors,
        private readonly DoctorProfileRepositoryInterface $doctorProfiles,
    ) {
    }

    public function __invoke(AssignSpecialtiesRequest $request): JsonResponse
    {
        $profile = $this->doctorProfiles->findByUserId($request->user()->id);
        $profile = $this->doctors->assignSpecialties($profile, $request->validated()["specialty_ids"]);

        return $this->apiResponse(new DoctorProfileResource($profile), "Specialties updated.");
    }
}
