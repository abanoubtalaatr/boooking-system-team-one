<?php

declare(strict_types=1);

use App\Contracts\Sms\SmsSenderInterface;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->sentMessages = [];

    $this->app->bind(SmsSenderInterface::class, function () {
        return new class($this->sentMessages) implements SmsSenderInterface
        {
            /**
             * @param  array<int, array{phone: string, message: string}>  $messages
             */
            public function __construct(private array &$messages) {}

            public function send(string $phone, string $message): void
            {
                $this->messages[] = [
                    'phone' => $phone,
                    'message' => $message,
                ];
            }
        };
    });
});

it('registers a patient and sends account verification otp', function (): void {
    $response = $this->postJson('/api/patient/register', [
        'name' => 'Ahmed Ali',
        'phone' => '01012345678',
        'email' => 'ahmed@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.patient.phone', '01012345678')
        ->assertJsonPath('data.patient.is_verified', false);

    $this->assertDatabaseHas('patients', [
        'phone' => '01012345678',
        'email' => 'ahmed@example.com',
        'verified_at' => null,
    ]);

    expect($this->sentMessages)->toHaveCount(1);
    expect($this->sentMessages[0]['phone'])->toBe('01012345678');
});

it('verifies a patient account with otp', function (): void {
    $this->postJson('/api/patient/register', [
        'name' => 'Ahmed Ali',
        'phone' => '01012345678',
        'email' => 'ahmed@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ])->assertCreated();

    preg_match('/\d{4}/', $this->sentMessages[0]['message'], $matches);

    $response = $this->postJson('/api/patient/verify-otp', [
        'phone' => '01012345678',
        'otp' => $matches[0],
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.patient.is_verified', true);

    expect(Patient::where('phone', '01012345678')->first()->isVerified())->toBeTrue();
});

it('logs in a verified patient with phone and password without sending otp', function (): void {
    Patient::factory()->create([
        'phone' => '01012345678',
        'password' => Hash::make('Password123'),
        'verified_at' => now(),
    ]);

    $response = $this->postJson('/api/patient/login', [
        'phone' => '01012345678',
        'password' => 'Password123',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonStructure([
            'data' => [
                'patient',
                'token',
                'token_type',
            ],
        ]);

    expect($this->sentMessages)->toHaveCount(0);
});

it('rejects login for an unverified patient', function (): void {
    Patient::factory()->unverified()->create([
        'phone' => '01012345678',
        'password' => Hash::make('Password123'),
    ]);

    $this->postJson('/api/patient/login', [
        'phone' => '01012345678',
        'password' => 'Password123',
    ])->assertUnprocessable();
});

it('rejects unsupported phone numbers', function (): void {
    $this->postJson('/api/patient/register', [
        'name' => 'Ahmed Ali',
        'phone' => '447700900123',
        'email' => 'ahmed@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('phone');
});

it('sends password reset otp and resets the password', function (): void {
    Patient::factory()->create([
        'phone' => '01012345678',
        'password' => Hash::make('OldPassword123'),
    ]);

    $this->postJson('/api/patient/forgot-password', [
        'phone' => '01012345678',
    ])->assertOk();

    preg_match('/\d{4}/', $this->sentMessages[0]['message'], $matches);

    $this->postJson('/api/patient/reset-password', [
        'phone' => '01012345678',
        'otp' => $matches[0],
        'password' => 'NewPassword123',
        'password_confirmation' => 'NewPassword123',
    ])->assertOk();

    $patient = Patient::where('phone', '01012345678')->first();

    expect(Hash::check('NewPassword123', $patient->password))->toBeTrue();
});

it('logs out the current patient token', function (): void {
    $patient = Patient::factory()->create();
    $token = $patient->createToken('patient-mobile')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/patient/logout')
        ->assertOk();

    expect(PersonalAccessToken::count())->toBe(0);
});
