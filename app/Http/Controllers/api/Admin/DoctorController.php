<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDoctorRequest;
use App\Http\Requests\Admin\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Models\DoctorProfile;
use App\Services\AdminDoctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class DoctorController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AdminDoctorService $doctors)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $doctors = $this->doctors->list($request->only(["specialty_id", "hospital_id", "is_approved"]));

        return $this->apiResponse(DoctorResource::collection($doctors));
    }

    public function store(StoreDoctorRequest $request): JsonResponse
    {
        $profile = $this->doctors->create($request->validated(), $request->user());

        return $this->apiResponse(new DoctorResource($profile), "Doctor account created.", 201);
    }

    public function show(DoctorProfile $doctor): JsonResponse
    {
        return $this->apiResponse(new DoctorResource($doctor->load(["specialties", "hospitals"])));
    }

    public function update(UpdateDoctorRequest $request, DoctorProfile $doctor): JsonResponse
    {
        $doctor = $this->doctors->update($doctor, $request->validated());

        return $this->apiResponse(new DoctorResource($doctor));
    }

    /** Soft "delete" = suspend, per spec (never hard-deletes a doctor account). */
    public function destroy(DoctorProfile $doctor): JsonResponse
    {
        $doctor = $this->doctors->suspend($doctor);

        return $this->apiResponse(new DoctorResource($doctor), "Doctor suspended.");
    }
}
