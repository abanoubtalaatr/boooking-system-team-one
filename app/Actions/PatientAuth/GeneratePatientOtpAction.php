<?php

declare(strict_types=1);

namespace App\Actions\PatientAuth;

use App\Contracts\Sms\SmsSenderInterface;
use App\Enums\PatientOtpType;
use App\Models\Patient;
use Illuminate\Support\Facades\Hash;

class GeneratePatientOtpAction
{
    public function __construct(
        private readonly SmsSenderInterface $smsSender,
    ) {}

    public function __invoke(Patient $patient, PatientOtpType $type): void
    {
        $plainCode = '1234';

        $patient->otps()
            ->where('type', $type)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $patient->otps()->create([
            'phone' => $patient->phone,
            'code' => Hash::make($plainCode),
            'type' => $type,
            'expires_at' => now()->addMinutes((int) config('auth.patient_otp_expire', 10)),
        ]);

        $this->smsSender->send(
            $patient->phone,
            "Your verification code is {$plainCode}."
        );
    }
}
