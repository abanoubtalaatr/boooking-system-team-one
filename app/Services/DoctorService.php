<?php

namespace App\Services;

use App\Models\DoctorProfile;
use App\Repositories\Contracts\DoctorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DoctorService
{
    public function __construct(
        protected DoctorRepositoryInterface $doctors
    ) {}

    public function list(Request $request): LengthAwarePaginator
    {
        $patient = $request->user('patient');
        return $this->doctors->paginate($request, $patient);
    }

    public function show(int $id, Request $request): DoctorProfile
    {
        $patient = $request->user('patient');
       
        $doctor = $this->doctors->findById($id, $request, $patient);

        if (!$doctor) {
            throw new NotFoundHttpException('Doctor not found');
        }

        return $doctor;
    }
}