<?php

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createBladeDashboardPayment(
    User $doctor,
    Patient $patient,
    PaymentMethod $method,
    PaymentStatus $status,
    int $amountCents,
): Payment {
    $slot = AvailabilitySlot::query()->where('doctor_id', $doctor->id)->firstOrFail();
    $booking = Booking::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'availability_slot_id' => $slot->id,
        'booking_date' => now()->addDay()->toDateString(),
        'status' => BookingStatus::Confirmed,
        'payment_status' => $status,
        'price' => number_format($amountCents / 100, 2, '.', ''),
    ]);

    return Payment::factory()->create([
        'booking_id' => $booking->id,
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'method' => $method,
        'status' => $status,
        'amount_cents' => $amountCents,
        'commission_bps' => 1000,
        'commission_amount_cents' => intdiv($amountCents * 10, 100),
        'doctor_amount_cents' => intdiv($amountCents * 90, 100),
        'paid_at' => in_array($status, [PaymentStatus::Succeeded, PaymentStatus::CashCollected], true) ? now() : null,
    ]);
}

test('payment Blade dashboards require authentication and the correct role', function () {
    $this->get('/admin')->assertRedirect(route('login'));
    $this->get('/doctor')->assertRedirect(route('login'));

    [$doctor] = createBookableSlot();
    $this->actingAs($doctor)->get('/admin')->assertForbidden();

    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get('/doctor')->assertForbidden();
});

test('admin Blade dashboard displays payments for every doctor and real financial totals', function () {
    $admin = User::factory()->create(['role' => 'admin', 'name' => 'مدير المنصة']);
    [$firstDoctor] = createBookableSlot();
    [$secondDoctor] = createBookableSlot();
    $firstDoctor->update(['name' => 'الطبيب الأول']);
    $secondDoctor->update(['name' => 'الطبيب الثاني']);
    $patient = Patient::factory()->create(['name' => 'مريض الاختبار']);

    createBladeDashboardPayment($firstDoctor, $patient, PaymentMethod::Card, PaymentStatus::Succeeded, 50000);
    createBladeDashboardPayment($secondDoctor, $patient, PaymentMethod::Cash, PaymentStatus::CashCollected, 30000);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertViewIs('admin.dashboard')
        ->assertViewHas('summary', fn (array $summary): bool => $summary['total_transactions'] === 2
            && $summary['gross_collected_cents'] === 80000
            && $summary['platform_fees_cents'] === 8000)
        ->assertSeeText('إدارة كل المدفوعات')
        ->assertSeeText('الطبيب الأول')
        ->assertSeeText('الطبيب الثاني')
        ->assertSeeText('مريض الاختبار');
});

test('admin Blade dashboard payment filters are applied', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    [$firstDoctor] = createBookableSlot();
    [$secondDoctor] = createBookableSlot();
    $firstDoctor->update(['name' => 'طبيب الفيزا']);
    $secondDoctor->update(['name' => 'طبيب الكاش']);
    $patient = Patient::factory()->create();

    createBladeDashboardPayment($firstDoctor, $patient, PaymentMethod::Card, PaymentStatus::Succeeded, 50000);
    createBladeDashboardPayment($secondDoctor, $patient, PaymentMethod::Cash, PaymentStatus::CashCollected, 30000);

    $this->actingAs($admin)
        ->get("/admin?doctor_id={$firstDoctor->id}&method=card&status=succeeded")
        ->assertOk()
        ->assertViewHas('payments', fn ($payments): bool => $payments->total() === 1
            && $payments->first()->doctor_id === $firstDoctor->id)
        ->assertSeeText('طبيب الفيزا');
});

test('doctor Blade dashboard never displays another doctor payments', function () {
    [$doctor] = createBookableSlot();
    [$otherDoctor] = createBookableSlot();
    $doctor->update(['name' => 'طبيبي الحالي']);
    $otherDoctor->update(['name' => 'طبيب غير مصرح']);
    $ownPatient = Patient::factory()->create(['name' => 'مريضي المسموح']);
    $otherPatient = Patient::factory()->create(['name' => 'مريض غير مسموح']);

    createBladeDashboardPayment($doctor, $ownPatient, PaymentMethod::Card, PaymentStatus::Succeeded, 50000);
    createBladeDashboardPayment($otherDoctor, $otherPatient, PaymentMethod::Card, PaymentStatus::Succeeded, 90000);

    $this->actingAs($doctor)
        ->get("/doctor?doctor_id={$otherDoctor->id}")
        ->assertOk()
        ->assertViewIs('doctor.dashboard')
        ->assertViewHas('payments', fn ($payments): bool => $payments->total() === 1
            && $payments->first()->doctor_id === $doctor->id)
        ->assertSeeText('مريضي المسموح')
        ->assertDontSeeText('مريض غير مسموح')
        ->assertDontSeeText('طبيب غير مصرح');
});
