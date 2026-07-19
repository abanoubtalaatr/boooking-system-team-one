<?php

namespace App\Actions\Doctor;

use App\Enums\UserStatus;
use App\Models\DoctorProfile;
use App\Models\User;
use App\Repositories\Contracts\DoctorProfileRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateDoctorAccountAction
{
    public function __construct(
        private readonly DoctorProfileRepositoryInterface $doctorProfiles,
    ) {}

    /**
     * Admin creates the users row + an empty doctor_profiles row in one transaction.
     */
    public function handle(array $data, User $admin): DoctorProfile
    {
        return DB::transaction(function () use ($data, $admin) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => UserStatus::PendingProfile,
                'created_by' => $admin->id,
            ]);
            $user->assignRole('doctor');

            return $this->doctorProfiles->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
