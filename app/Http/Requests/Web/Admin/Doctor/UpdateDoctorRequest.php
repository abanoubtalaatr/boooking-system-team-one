<?php

namespace App\Http\Requests\Web\Admin\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specialization_id' => ['nullable', 'exists:specializations,id'],
            'hospital_id'       => ['nullable', 'exists:hospitals,id'],
            'is_active'         => ['required', 'boolean'],
        ];
    }
}