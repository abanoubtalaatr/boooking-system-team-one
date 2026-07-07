<?php

declare(strict_types=1);

namespace App\Actions\PatientAuth;

use App\Enums\PatientOtpType;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class ResetPatientPasswordAction
{
    public function __construct(
        private readonly VerifyPatientOtpAction $verifyPatientOtp,
    ) {}

    public function __invoke(Patient $patient, string $otp, string $password): Patient
    {
        return DB::transaction(function () use ($patient, $otp, $password): Patient {
            ($this->verifyPatientOtp)($patient, $otp, PatientOtpType::PasswordReset);

            $patient->forceFill([
                'password' => $password,
            ])->save();

            $patient->tokens()->delete();

            return $patient->refresh();
        });
    }
}
