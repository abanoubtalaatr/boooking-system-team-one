<?php

use App\Models\AvailabilitySlot;
use App\Models\DoctorProfile;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\DuskTestCase;
use Tests\TestCase;

pest()->extend(DuskTestCase::class)
//  ->use(Illuminate\Foundation\Testing\DatabaseMigrations::class)
    ->in('Browser');

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function createBookableSlot(): array
{
    $doctor = User::factory()->create(['role' => 'doctor']);
    $specialization = Specialization::factory()->create(['name' => fake()->unique()->jobTitle()]);
    $hospital = Hospital::factory()->create();
    DoctorProfile::factory()->create([
        'user_id' => $doctor->id,
        'specialization_id' => $specialization->id,
        'hospital_id' => $hospital->id,
        'price' => 500,
    ]);
    $slot = AvailabilitySlot::factory()->create([
        'doctor_id' => $doctor->id,
        'day' => now()->addDay()->toDateString(),
        'is_booked' => false,
    ]);

    return [$doctor, $slot];
}

function createBookingThroughApi($test, Patient $patient, User $doctor, AvailabilitySlot $slot, string $key = 'booking-key'): int
{
    Sanctum::actingAs($patient, ['*'], 'patient');

    return (int) $test->withHeader('Idempotency-Key', $key)
        ->postJson('/api/bookings', [
            'doctor_id' => $doctor->id,
            'availability_slot_id' => $slot->id,
            'consultation_type' => 'clinic',
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending_payment')
        ->json('data.id');
}
