<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class AssignSpecialtiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isDoctor() ?? false;
    }

    public function rules(): array
    {
        return [
            'specialization_ids' => ['required', 'array', 'min:1'],
            'specialization_ids.*' => ['required', 'uuid', 'exists:specialties,id'],
        ];
    }
}
