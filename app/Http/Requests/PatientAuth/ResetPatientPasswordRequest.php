<?php

declare(strict_types=1);

namespace App\Http\Requests\PatientAuth;

use App\Rules\SupportedPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPatientPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', new SupportedPhoneNumber, 'exists:patients,phone'],
            'otp' => ['required', 'string', 'digits:4'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }
}
