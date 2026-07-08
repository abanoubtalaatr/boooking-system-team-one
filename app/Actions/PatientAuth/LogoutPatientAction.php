<?php

declare(strict_types=1);

namespace App\Actions\PatientAuth;

use App\Models\Patient;

class LogoutPatientAction
{
    public function __invoke(Patient $patient): void
    {
        $patient->currentAccessToken()?->delete();
    }
}
