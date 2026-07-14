<?php

namespace App\Actions\Doctor;

use App\Models\DoctorProfile;
use App\Repositories\Contracts\SpecialtyRepositoryInterface;

class AssignSpecialtiesToDoctorAction
{
    public function __construct(
        private readonly SpecialtyRepositoryInterface $specialties,
    ) {
    }

    /**
     * Sync (not attach) so the given IDs become the doctors full specialty set.
     */
    public function handle(DoctorProfile $profile, array $specialtyIds): DoctorProfile
    {
        // Validated to exist in SpecialtyRepositoryInterface::findManyByIds via the Form Request.
        $profile->specialties()->sync($specialtyIds);

        return $profile->refresh()->load("specialties");
    }
}
