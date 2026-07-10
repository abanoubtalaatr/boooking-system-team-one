<?php

declare(strict_types=1);

namespace App\Actions\PatientAuth;

use App\Models\Patient;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginPatientAction
{
    /**
     * @return array{patient: Patient, token: string}
     */
    public function __invoke(string $phone, string $password): array
    {
        $patient = Patient::query()
            ->where('phone', $phone)
            ->first();

        if (! $patient || ! Hash::check($password, $patient->password)) {
            throw ValidationException::withMessages([
                'phone' => __('The provided credentials are incorrect.'),
            ]);
        }

        if (! $patient->isVerified()) {
            throw ValidationException::withMessages([
                'phone' => __('Please verify your account before logging in.'),
            ]);
        }

        return [
            'patient' => $patient,
            'token' => $patient->createToken('patient-mobile')->plainTextToken,
        ];
    }
}
