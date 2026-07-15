<?php

use App\Enums\BookingStatus;
use App\Jobs\CompletePendingBookings;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\BookingCompletedNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

uses(LazilyRefreshDatabase::class);

it('completes only ended pending bookings and notifies their doctor', function (): void {
    $this->travelTo('2026-07-15 12:00:00');
    $doctor = User::factory()->create(['role' => 'doctor']);
    $patient = Patient::factory()->create();
    $endedSlot = AvailabilitySlot::factory()->create([
        'doctor_id' => $doctor->id,
        'day' => today(),
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
    ]);
    $futureSlot = AvailabilitySlot::factory()->create([
        'doctor_id' => $doctor->id,
        'day' => today(),
        'start_time' => '13:00:00',
        'end_time' => '14:00:00',
    ]);
    $endedPendingBooking = Booking::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'availability_slot_id' => $endedSlot->id,
        'booking_date' => today(),
        'booking_time' => '10:00:00',
        'status' => BookingStatus::Pending,
    ]);
    $futurePendingBooking = Booking::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'availability_slot_id' => $futureSlot->id,
        'booking_date' => today(),
        'booking_time' => '13:00:00',
        'status' => BookingStatus::Pending,
    ]);
    $endedConfirmedBooking = Booking::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'availability_slot_id' => $endedSlot->id,
        'booking_date' => today(),
        'booking_time' => '10:00:00',
        'status' => BookingStatus::Confirmed,
    ]);
    Notification::fake();

    (new CompletePendingBookings)->handle();
    (new CompletePendingBookings)->handle();

    expect($endedPendingBooking->fresh()->status)->toBe(BookingStatus::Completed)
        ->and($futurePendingBooking->fresh()->status)->toBe(BookingStatus::Pending)
        ->and($endedConfirmedBooking->fresh()->status)->toBe(BookingStatus::Confirmed);

    Notification::assertSentTo(
        $doctor,
        BookingCompletedNotification::class,
        function (BookingCompletedNotification $notification) use ($endedPendingBooking): bool {
            return $notification->booking->is($endedPendingBooking)
                && $notification->toArray($notification->booking->doctor)['message']
                    === 'تم تحويل الحجز إلى مكتمل. إذا كانت هناك حالة مشكوك فيها، يرجى التواصل مع الدعم.';
        },
    );
    Notification::assertSentToTimes($doctor, BookingCompletedNotification::class, 1);
});

it('registers the pending booking completion job as a daily schedule', function (): void {
    Artisan::call('schedule:list');

    expect(Artisan::output())->toContain(CompletePendingBookings::class);
});
