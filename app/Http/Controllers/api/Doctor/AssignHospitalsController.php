<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\AssignHospitalsRequest;
use App\Http\Resources\DoctorProfileResource;
use App\Repositories\Contracts\DoctorProfileRepositoryInterface;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class AssignHospitalsController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DoctorService $doctors,
        private readonly DoctorProfileRepositoryInterface $doctorProfiles,
    ) {
    }

    public function __invoke(AssignHospitalsRequest $request): JsonResponse
    {
        $profile = $this->doctorProfiles->findByUserId($request->user()->id);
        $profile = $this->doctors->assignHospitals($profile, $request->validated()["hospital_ids"]);

        return $this->apiResponse(new DoctorProfileResource($profile), "Hospitals updated.");
    }
}
