<?php

namespace App\Services;

use App\Actions\Doctor\ApproveDoctorAction;
use App\Actions\Doctor\CreateDoctorAccountAction;
use App\Actions\Doctor\SuspendDoctorAction;
use App\Models\DoctorProfile;
use App\Models\User;
use App\Repositories\Contracts\DoctorProfileRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Admin-only orchestration for doctor accounts.
 */
class AdminDoctorService
{
    public function __construct(
        private readonly CreateDoctorAccountAction $createDoctorAccount,
        private readonly ApproveDoctorAction $approveDoctor,
        private readonly SuspendDoctorAction $suspendDoctor,
        private readonly DoctorProfileRepositoryInterface $doctorProfiles,
    ) {
    }

    public function create(array $data, User $admin): DoctorProfile
    {
        return $this->createDoctorAccount->handle($data, $admin);
    }

    public function approve(DoctorProfile $profile): DoctorProfile
    {
        return $this->approveDoctor->handle($profile);
    }

    public function suspend(DoctorProfile $profile): DoctorProfile
    {
        return $this->suspendDoctor->handle($profile);
    }

    public function find(string $id): ?DoctorProfile
    {
        return $this->doctorProfiles->find($id);
    }

    public function update(DoctorProfile $profile, array $data): DoctorProfile
    {
        return $this->doctorProfiles->update($profile, $data);
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->doctorProfiles->paginate($filters, $perPage);
    }
}
