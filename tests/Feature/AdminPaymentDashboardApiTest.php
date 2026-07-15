<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('admin payment list requires admin authentication', function () {
    $this->getJson('/api/admin/payments')->assertUnauthorized();

    [$doctor] = createBookableSlot();
    Sanctum::actingAs($doctor);

    $this->getJson('/api/admin/payments')->assertForbidden();
});

test('admin sees all payments and can filter them by doctor and payment state', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    [$firstDoctor] = createBookableSlot();
    [$secondDoctor] = createBookableSlot();
    $patient = Patient::factory()->create();
    $firstPayment = createDoctorDashboardPayment(
        $firstDoctor,
        $patient,
        PaymentMethod::Card,
        PaymentStatus::Succeeded,
        50000,
        5000,
    );
    $secondPayment = createDoctorDashboardPayment(
        $secondDoctor,
        $patient,
        PaymentMethod::Cash,
        PaymentStatus::CashCollected,
        30000,
        3000,
    );

    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/payments')
        ->assertOk()
        ->assertJsonPath('meta.total', 2)
        ->assertJsonFragment(['uuid' => $firstPayment->uuid])
        ->assertJsonFragment(['uuid' => $secondPayment->uuid]);

    $this->getJson("/api/admin/payments?doctor_id={$firstDoctor->id}&method=card&status=succeeded")
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.uuid', $firstPayment->uuid)
        ->assertJsonPath('data.0.doctor.id', $firstDoctor->id)
        ->assertJsonMissing(['uuid' => $secondPayment->uuid]);
});

test('dashboard payment filters are validated', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/payments?status=unknown&per_page=101&date_from=2026-07-14&date_to=2026-07-13')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status', 'per_page', 'date_to']);
});
