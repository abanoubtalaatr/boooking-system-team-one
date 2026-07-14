<?php

namespace App\Repositories\Contracts;

use App\Models\DoctorProfile;
use App\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface DoctorRepositoryInterface
{
    public function paginate(Request $request, ?Patient $patient = null): LengthAwarePaginator;

    public function findById(int $id, Request $request, ?Patient $patient = null): ?DoctorProfile;
}