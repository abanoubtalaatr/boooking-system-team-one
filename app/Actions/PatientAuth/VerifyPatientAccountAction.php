<?php

declare(strict_types=1);

namespace App\Actions\PatientAuth;

use App\Enums\PatientOtpType;
use App\Models\Patient;

class VerifyPatientAccountAction
{
    public function __construct(
        private readonly VerifyPatientOtpAction $verifyPatientOtp,
    ) {}

    public function __invoke(Patient $patient, string $otp): Patient
    {
        ($this->verifyPatientOtp)($patient, $otp, PatientOtpType::AccountVerification);

        if (! $patient->isVerified()) {
            $patient->forceFill([
                'verified_at' => now(),
            ])->save();
        }

        return $patient->refresh();
    }
}
