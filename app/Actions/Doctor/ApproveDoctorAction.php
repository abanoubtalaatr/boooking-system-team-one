<?php

namespace App\Actions\Doctor;

use App\Models\DoctorProfile;
use App\Repositories\Contracts\DoctorProfileRepositoryInterface;

class ApproveDoctorAction
{
    public function __construct(
        private readonly DoctorProfileRepositoryInterface $doctorProfiles,
    ) {
    }

    public function handle(DoctorProfile $profile): DoctorProfile
    {
        return $this->doctorProfiles->update($profile, ["is_approved" => true]);
    }
}
