<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\CompleteDoctorProfileRequest;
use App\Http\Resources\DoctorProfileResource;
use App\Repositories\Contracts\DoctorProfileRepositoryInterface;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

/**
 * Resource controller kept to the 5 REST verbs by convention, but only
 * show/update are meaningful for "my own profile":
 * - index: n/a (a doctor has exactly one profile) -> 405
 * - store: n/a (profile is created by CreateDoctorAccountAction, not self) -> 405
 * - destroy: n/a (a doctor never deletes their own account) -> 405
 */
class ProfileController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DoctorProfileRepositoryInterface $doctorProfiles,
        private readonly DoctorService $doctors,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->errorResponse("Not supported.", 405);
    }

    public function store(): JsonResponse
    {
        return $this->errorResponse("Not supported.", 405);
    }

    public function show(Request $request): JsonResponse
    {
        $profile = $this->doctorProfiles->findByUserId($request->user()->id);

        return $this->apiResponse(new DoctorProfileResource($profile));
    }

    public function update(CompleteDoctorProfileRequest $request): JsonResponse
    {
        $profile = $this->doctorProfiles->findByUserId($request->user()->id);
        $profile = $this->doctors->completeProfile($profile, $request->validated());

        return $this->apiResponse(new DoctorProfileResource($profile), "Profile updated.");
    }

    public function destroy(): JsonResponse
    {
        return $this->errorResponse("Not supported.", 405);
    }
}
