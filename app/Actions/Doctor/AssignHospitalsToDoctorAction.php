<?php

namespace App\Actions\Doctor;

use App\Models\DoctorProfile;

class AssignHospitalsToDoctorAction
{
    /**
     * Sync (not attach) so the given IDs become the doctors full hospital set.
     */
    public function handle(DoctorProfile $profile, array $hospitalIds): DoctorProfile
    {
        $profile->hospitals()->sync($hospitalIds);

        return $profile->refresh()->load("hospitals");
    }
}
