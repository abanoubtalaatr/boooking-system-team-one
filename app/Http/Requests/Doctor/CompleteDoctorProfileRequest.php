<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class CompleteDoctorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isDoctor() ?? false;
    }

    public function rules(): array
    {
        return [
            'bio' => ['required', 'string', 'max:5000'],
            'consultation_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
