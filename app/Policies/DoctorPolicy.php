<?php

namespace App\Policies;

use App\Models\DoctorProfile;
use App\Models\User;

class DoctorPolicy
{
    public function viewSelf(User $user, DoctorProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }

    public function update(User $user, DoctorProfile $profile): bool
    {
        return $user->role === "admin" || $user->id === $profile->user_id;
    }

    public function manage(User $user): bool
    {
        return $user->role === "admin";
    }
}
