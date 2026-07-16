<?php

namespace App\Services\Web;

use App\Mail\DoctorAccountCreatedMail;
use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class DoctorAccountService
{
    public function createDoctor(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $doctor = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => 'doctor',
            ]);

            DoctorProfile::create([
                'user_id'           => $doctor->id,
                'specialization_id' => $data['specialization_id'],
                'hospital_id'       => $data['hospital_id'],
            ]);

            Mail::to($doctor->email)->send(
                new DoctorAccountCreatedMail($doctor, $data['password'])
            );

            return $doctor;
        });
    }

    public function updateDoctor(User $doctor, array $data): DoctorProfile
    {
        return $doctor->doctorProfile()->updateOrCreate(
            ['user_id' => $doctor->id],
            [
                'specialization_id' => $data['specialization_id'],
                'hospital_id'       => $data['hospital_id'],
                'is_active'         => $data['is_active'],
            ]
        );
    }

    /**
     * Delete the doctor only if he has no related bookings, availability slots,
     * or conversations. Returns true on success, false when blocked.
     */
    public function deleteDoctor(User $doctor): bool
    {
        if ($this->deletionBlockReason($doctor) !== null) {
            return false;
        }

        return (bool) $doctor->delete();
    }

    /**
     * Returns an Arabic message explaining why the doctor cannot be deleted,
     * or null if deletion is allowed.
     *
     * NOTE: relation names (bookingsAsDoctor, conversationsAsDoctor) must match
     * the actual relations defined on the User / DoctorProfile models.
     */
    public function deletionBlockReason(User $doctor): ?string
    {
        if ($doctor->bookingsAsDoctor()->exists()) {
            return 'لا يمكن حذف الطبيب لوجود حجوزات مرتبطة به.';
        }

        if ($doctor->doctorProfile && $doctor->doctorProfile->availabilitySlots()->exists()) {
            return 'لا يمكن حذف الطبيب لوجود مواعيد متاحة (Slots) مرتبطة به.';
        }

        if (method_exists($doctor, 'conversationsAsDoctor') && $doctor->conversationsAsDoctor()->exists()) {
            return 'لا يمكن حذف الطبيب لوجود محادثات مرتبطة به.';
        }

        return null;
    }
}