<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class AssignHospitalsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isDoctor() ?? false;
    }

    public function rules(): array
    {
        return [
            'hospital_ids' => ['required', 'array', 'min:1'],
            'hospital_ids.*' => ['required', 'uuid', 'exists:hospitals,id'],
        ];
    }
}
