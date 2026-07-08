<?php

namespace App\Repositories\Eloquent;

use App\Models\DoctorProfile;
use App\Repositories\Contracts\DoctorProfileRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentDoctorProfileRepository implements DoctorProfileRepositoryInterface
{
    public function find(string $id): ?DoctorProfile
    {
        return DoctorProfile::with(["user", "specialties", "hospitals"])->find($id);
    }

    public function findByUserId(string $userId): ?DoctorProfile
    {
        return DoctorProfile::with(["user", "specialties", "hospitals"])
            ->where("user_id", $userId)
            ->first();
    }

    public function create(array $data): DoctorProfile
    {
        return DoctorProfile::create($data);
    }

    public function update(DoctorProfile $profile, array $data): DoctorProfile
    {
        $profile->update($data);

        return $profile->refresh();
    }

    public function delete(DoctorProfile $profile): bool
    {
        return (bool) $profile->delete();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DoctorProfile::with(["user", "specialties", "hospitals"]);

        if (! empty($filters["specialty_id"])) {
            $query->whereHas("specialties", fn ($q) => $q->where("specialties.id", $filters["specialty_id"]));
        }

        if (! empty($filters["hospital_id"])) {
            $query->whereHas("hospitals", fn ($q) => $q->where("hospitals.id", $filters["hospital_id"]));
        }

        if (array_key_exists("is_approved", $filters)) {
            $query->where("is_approved", $filters["is_approved"]);
        }

        return $query->paginate($perPage);
    }
}
