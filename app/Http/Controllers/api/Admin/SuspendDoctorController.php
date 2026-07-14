<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Models\DoctorProfile;
use App\Services\AdminDoctorService;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class SuspendDoctorController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AdminDoctorService $doctors)
    {
    }

    public function __invoke(DoctorProfile $doctor): JsonResponse
    {
        $doctor = $this->doctors->suspend($doctor);

        return $this->apiResponse(new DoctorResource($doctor), "Doctor suspended.");
    }
}
