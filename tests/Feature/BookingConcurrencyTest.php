<?php

use App\Actions\Booking\CreateBookingAction;
use App\Enums\SlotReservationStatus;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\DoctorProfile;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

it('allows exactly one of two concurrent patients to hold the same slot', function (): void {
    $doctor = User::factory()->doctor()->create();
    $specialization = Specialization::factory()->create(['name' => 'Concurrency '.Str::uuid()]);
    $hospital = Hospital::factory()->create();
    DoctorProfile::factory()->create([
        'user_id' => $doctor->id,
        'specialization_id' => $specialization->id,
        'hospital_id' => $hospital->id,
        'price' => 500,
        'is_active' => true,
    ]);
    $slot = AvailabilitySlot::factory()->create([
        'doctor_id' => $doctor->id,
        'day' => now()->addDay()->toDateString(),
        'is_booked' => false,
    ]);
    $firstPatient = Patient::factory()->create();
    $secondPatient = Patient::factory()->create();
    $baseData = [
        'doctor_id' => $doctor->id,
        'availability_slot_id' => $slot->id,
        'consultation_type' => 'clinic',
    ];
    $firstPatientId = $firstPatient->id;
    $secondPatientId = $secondPatient->id;
    $firstAttempt = Closure::bind(static function () use ($baseData, $firstPatientId): string {
        try {
            app(CreateBookingAction::class)(
                [...$baseData, 'idempotency_key' => 'concurrent-first'],
                $firstPatientId,
            );

            return 'created';
        } catch (ValidationException) {
            return 'rejected';
        }
    }, null, null);
    $secondAttempt = Closure::bind(static function () use ($baseData, $secondPatientId): string {
        try {
            app(CreateBookingAction::class)(
                [...$baseData, 'idempotency_key' => 'concurrent-second'],
                $secondPatientId,
            );

            return 'created';
        } catch (ValidationException) {
            return 'rejected';
        }
    }, null, null);

    try {
        $results = Concurrency::run([$firstAttempt, $secondAttempt]);

        expect(collect($results)->where(fn (string $result): bool => $result === 'created'))->toHaveCount(1)
            ->and(collect($results)->where(fn (string $result): bool => $result === 'rejected'))->toHaveCount(1)
            ->and(Booking::query()->where('availability_slot_id', $slot->id)->count())->toBe(1)
            ->and($slot->fresh()->reservation_status)->toBe(SlotReservationStatus::Held);
    } finally {
        $firstPatient->delete();
        $secondPatient->delete();
        $doctor->delete();
        $specialization->delete();
        $hospital->delete();
    }
})->skip(fn (): bool => DB::getDriverName() !== 'mysql', 'Requires MySQL row locks and separate processes.');
