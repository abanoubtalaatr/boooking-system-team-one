<?php

namespace App\Actions\Doctor;

use App\Enums\UserStatus;
use App\Models\DoctorProfile;
use App\Repositories\Contracts\DoctorProfileRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CompleteDoctorProfileAction
{
    public function __construct(
        private readonly DoctorProfileRepositoryInterface $doctorProfiles,
    ) {
    }

    /**
     * Doctor fills bio/price on first login; flips users.status to active.
     */
    public function handle(DoctorProfile $profile, array $data): DoctorProfile
    {
        return DB::transaction(function () use ($profile, $data) {
            $profile = $this->doctorProfiles->update($profile, [
                "bio" => $data["bio"] ?? $profile->bio,
                "consultation_price" => $data["consultation_price"] ?? $profile->consultation_price,
            ]);

            $profile->user()->update(["status" => UserStatus::Active]);

            return $profile;
        });
    }
}
