<?php

namespace App\Services;

use App\Actions\Doctor\AssignHospitalsToDoctorAction;
use App\Actions\Doctor\AssignSpecialtiesToDoctorAction;
use App\Actions\Doctor\CompleteDoctorProfileAction;
use App\Actions\Doctor\CreateAvailabilitySlotAction;
use App\Actions\Doctor\AcceptBookingAction;
use App\Actions\Doctor\RejectBookingAction;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\DoctorProfile;
use App\Repositories\Contracts\AvailabilitySlotRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Single entry point controllers use for doctor self-service operations.
 */
class DoctorService
{
    public function __construct(
        private readonly CompleteDoctorProfileAction $completeDoctorProfile,
        private readonly AssignSpecialtiesToDoctorAction $assignSpecialties,
        private readonly AssignHospitalsToDoctorAction $assignHospitals,
        private readonly CreateAvailabilitySlotAction $createAvailabilitySlot,
        private readonly AcceptBookingAction $acceptBooking,
        private readonly RejectBookingAction $rejectBooking,
        private readonly AvailabilitySlotRepositoryInterface $slots,
    ) {
    }

    public function completeProfile(DoctorProfile $profile, array $data): DoctorProfile
    {
        return $this->completeDoctorProfile->handle($profile, $data);
    }

    public function assignSpecialties(DoctorProfile $profile, array $specialtyIds): DoctorProfile
    {
        return $this->assignSpecialties->handle($profile, $specialtyIds);
    }

    public function assignHospitals(DoctorProfile $profile, array $hospitalIds): DoctorProfile
    {
        return $this->assignHospitals->handle($profile, $hospitalIds);
    }

    public function createAvailabilitySlot(DoctorProfile $profile, array $data): AvailabilitySlot
    {
        return $this->createAvailabilitySlot->handle($profile, $data);
    }

    public function updateAvailabilitySlot(AvailabilitySlot $slot, array $data): AvailabilitySlot
    {
        $slot->update($data);

        return $slot->refresh();
    }

    public function deleteAvailabilitySlot(AvailabilitySlot $slot): bool
    {
        return $this->slots->delete($slot);
    }

    public function paginateAvailabilitySlots(string $doctorId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->slots->paginate($doctorId, $perPage);
    }

    public function acceptBooking(Booking $booking): Booking
    {
        return $this->acceptBooking->handle($booking);
    }

    public function rejectBooking(Booking $booking): Booking
    {
        return $this->rejectBooking->handle($booking);
    }
}
