<?php

declare(strict_types=1);

namespace App\Enums;

enum PatientOtpType: string
{
    case AccountVerification = 'account_verification';
    case PasswordReset = 'password_reset';
}
