<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SupportedPhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^(?:\+?20|0)1[0125][0-9]{8}$|^(?:\+?966|0)?5[0-9]{8}$/', $value)) {
            $fail(__('The :attribute must be a valid Egyptian or Saudi phone number.'));
        }
    }
}
