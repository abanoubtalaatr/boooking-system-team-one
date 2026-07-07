<?php

declare(strict_types=1);

namespace App\Actions\PatientAuth;

use App\Enums\PatientOtpType;
use App\Models\Patient;
use App\Models\PatientOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class VerifyPatientOtpAction
{
    public function __invoke(Patient $patient, string $code, PatientOtpType $type): PatientOtp
    {
        $otp = $patient->otps()
            ->where('type', $type)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (! $otp || $otp->isExpired() || ! Hash::check($code, $otp->code)) {
            throw ValidationException::withMessages([
                'otp' => __('The OTP code is invalid or expired.'),
            ]);
        }

        $otp->forceFill([
            'used_at' => now(),
        ])->save();

        return $otp;
    }
}
