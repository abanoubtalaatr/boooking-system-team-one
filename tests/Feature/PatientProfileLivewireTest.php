<?php

use App\Enums\BookingStatus;
use App\Enums\ConsultationType;
use App\Enums\PaymentMethod;
use App\Livewire\Admin\PatientProfile;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\DoctorProfile;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->restrictedAdmin()->create();
    $this->admin->givePermissionTo('patients.view');
    $this->patient = Patient::factory()->create([
        'name' => 'أحمد المريض',
        'phone' => '01012345678',
        'email' => 'patient.profile@example.test',
        'birthdate' => '1990-04-15',
    ]);
    $specialty = Specialization::factory()->create(['name' => 'طب القلب']);
    $this->visitedDoctor = User::factory()->doctor()->create(['name' => 'سارة محمود', 'email' => 'sara.doctor@example.test']);
    DoctorProfile::factory()->create(['user_id' => $this->visitedDoctor->id, 'specialization_id' => $specialty->id]);
    $visitedSlot = AvailabilitySlot::factory()->create([
        'doctor_id' => $this->visitedDoctor->id,
        'day' => '2026-07-10',
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
    ]);
    $this->cashBooking = Booking::factory()->create([
        'booking_number' => 'BK-PROFILE-CASH',
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->visitedDoctor->id,
        'availability_slot_id' => $visitedSlot->id,
        'booking_date' => '2026-07-10',
        'booking_time' => '10:00:00',
        'consultation_type' => ConsultationType::Clinic,
        'status' => BookingStatus::Completed,
    ]);
    Payment::factory()->create([
        'booking_id' => $this->cashBooking->id,
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->visitedDoctor->id,
        'method' => PaymentMethod::Cash,
    ]);

    $this->cardDoctor = User::factory()->doctor()->create(['name' => 'خالد علي', 'email' => 'khaled.doctor@example.test']);
    DoctorProfile::factory()->create(['user_id' => $this->cardDoctor->id, 'specialization_id' => $specialty->id]);
    $cardSlot = AvailabilitySlot::factory()->create([
        'doctor_id' => $this->cardDoctor->id,
        'day' => '2026-07-12',
        'start_time' => '14:00:00',
        'end_time' => '15:00:00',
    ]);
    $this->cardBooking = Booking::factory()->create([
        'booking_number' => 'BK-PROFILE-CARD',
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->cardDoctor->id,
        'availability_slot_id' => $cardSlot->id,
        'booking_date' => '2026-07-12',
        'booking_time' => '14:00:00',
        'consultation_type' => ConsultationType::Online,
        'status' => BookingStatus::Confirmed,
    ]);
    Payment::factory()->create([
        'booking_id' => $this->cardBooking->id,
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->cardDoctor->id,
        'method' => PaymentMethod::Card,
    ]);
    Review::factory()->create([
        'patient_id' => $this->patient->id,
        'user_id' => $this->visitedDoctor->id,
        'rating' => 5,
        'comment' => 'طبيبة ممتازة وموعد منظم',
        'created_at' => '2026-07-11 12:00:00',
    ]);
});

it('opens the patient profile from an eye icon on the patients page', function (): void {
    $this->actingAs($this->admin)
        ->get(route('admin.patients'))
        ->assertOk()
        ->assertSee(route('admin.patients.show', $this->patient), false)
        ->assertSee('عرض بروفايل أحمد المريض');

    $this->actingAs($this->admin)
        ->get(route('admin.patients.show', $this->patient))
        ->assertOk()
        ->assertSee('أحمد المريض')
        ->assertSee('01012345678');
});

it('shows bookings with doctor appointment type payment method and status', function (): void {
    Livewire::actingAs($this->admin)->test(PatientProfile::class, ['patient' => $this->patient])
        ->assertSee('BK-PROFILE-CASH')
        ->assertSee('BK-PROFILE-CARD')
        ->assertSee('د. سارة محمود')
        ->assertSee('10/07/2026')
        ->assertSee('في العيادة')
        ->assertSee('كاش')
        ->assertSee('فيزا')
        ->assertSee('مكتمل')
        ->assertSee('مؤكد');
});

it('shows only completed appointments in the visited doctors tab', function (): void {
    Livewire::actingAs($this->admin)->test(PatientProfile::class, ['patient' => $this->patient])
        ->call('setTab', 'visits')
        ->assertSet('activeTab', 'visits')
        ->assertSee('د. سارة محمود')
        ->assertSee('طب القلب')
        ->assertSee('10/07/2026')
        ->assertDontSee('د. خالد علي');
});

it('shows the patient ratings and comments with their doctors', function (): void {
    Livewire::actingAs($this->admin)->test(PatientProfile::class, ['patient' => $this->patient])
        ->call('setTab', 'reviews')
        ->assertSet('activeTab', 'reviews')
        ->assertSee('د. سارة محمود')
        ->assertSee('طبيبة ممتازة وموعد منظم')
        ->assertSee('5/5')
        ->assertSee('11/07/2026');
});

it('filters the active livewire table without leaving the profile', function (): void {
    Livewire::actingAs($this->admin)->test(PatientProfile::class, ['patient' => $this->patient])
        ->set('search', 'خالد')
        ->assertSee('BK-PROFILE-CARD')
        ->assertDontSee('BK-PROFILE-CASH')
        ->call('setTab', 'reviews')
        ->set('search', 'ممتازة')
        ->assertSee('طبيبة ممتازة وموعد منظم');
});

it('never exposes bookings or reviews belonging to another patient', function (): void {
    $otherPatient = Patient::factory()->create();
    Booking::query()->whereKey($this->cardBooking->id)->update(['patient_id' => $otherPatient->id]);
    Review::factory()->create([
        'patient_id' => $otherPatient->id,
        'user_id' => $this->cardDoctor->id,
        'comment' => 'تعليق خاص بمريض آخر',
    ]);

    Livewire::actingAs($this->admin)->test(PatientProfile::class, ['patient' => $this->patient])
        ->assertDontSee('BK-PROFILE-CARD')
        ->call('setTab', 'reviews')
        ->assertDontSee('تعليق خاص بمريض آخر');
});

it('forbids profile access without the patients view permission', function (): void {
    $restrictedAdmin = User::factory()->restrictedAdmin()->create();

    $this->actingAs($restrictedAdmin)
        ->get(route('admin.patients.show', $this->patient))
        ->assertForbidden();
});
