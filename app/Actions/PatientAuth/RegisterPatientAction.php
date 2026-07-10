<?php

declare(strict_types=1);

namespace App\Actions\PatientAuth;

use App\Enums\PatientOtpType;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class RegisterPatientAction
{
    public function __construct(
        private readonly GeneratePatientOtpAction $generatePatientOtp,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(array $data): Patient
    {
        return DB::transaction(function () use ($data): Patient {
            $patient = Patient::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            ($this->generatePatientOtp)($patient, PatientOtpType::AccountVerification);

            return $patient;
        });
    }
}
