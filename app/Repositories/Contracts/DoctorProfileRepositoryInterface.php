<?php

namespace App\Repositories\Contracts;

use App\Models\DoctorProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DoctorProfileRepositoryInterface
{
    public function find(string $id): ?DoctorProfile;

    public function findByUserId(string $userId): ?DoctorProfile;

    public function create(array $data): DoctorProfile;

    public function update(DoctorProfile $profile, array $data): DoctorProfile;

    public function delete(DoctorProfile $profile): bool;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
