<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Actions\Doctor\GetDoctorDashboardAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorDashboardResource;
use App\Models\User;
use Illuminate\Http\Request;

class DoctorDashboardController extends Controller
{
    public function __construct(private readonly GetDoctorDashboardAction $dashboard) {}

    public function __invoke(Request $request): DoctorDashboardResource
    {
        /** @var User $doctor */
        $doctor = $request->user();

        return new DoctorDashboardResource($this->dashboard->handle($doctor));
    }
}
