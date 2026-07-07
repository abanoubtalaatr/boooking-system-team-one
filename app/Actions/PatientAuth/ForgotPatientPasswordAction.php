<?php

declare(strict_types=1);

namespace App\Actions\PatientAuth;

use App\Enums\PatientOtpType;
use App\Models\Patient;

class ForgotPatientPasswordAction
{
    public function __construct(
        private readonly GeneratePatientOtpAction $generatePatientOtp,
    ) {}

    public function __invoke(string $phone): void
    {
        $patient = Patient::query()
            ->where('phone', $phone)
            ->firstOrFail();

        ($this->generatePatientOtp)($patient, PatientOtpType::PasswordReset);
    }
}
