<?php

declare(strict_types=1);

namespace App\Http\Requests\PatientAuth;

use Illuminate\Foundation\Http\FormRequest;

class LoginPatientRequest extends FormRequest
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
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
