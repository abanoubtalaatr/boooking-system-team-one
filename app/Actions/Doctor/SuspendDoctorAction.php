<?php

namespace App\Actions\Doctor;

use App\Enums\UserStatus;
use App\Models\DoctorProfile;

class SuspendDoctorAction
{
    /**
     * Suspends the underlying user account; used as the destroy() handler
     * on Admin\DoctorController instead of an actual delete.
     */
    public function handle(DoctorProfile $profile): DoctorProfile
    {
        $profile->user()->update(["status" => UserStatus::Suspended]);

        return $profile->refresh();
    }
}
