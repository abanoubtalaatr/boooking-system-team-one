<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class AssignSpecialtiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === "doctor";
    }

    public function rules(): array
    {
        return [
            "specialty_ids" => ["required", "array", "min:1"],
            "specialty_ids.*" => ["required", "uuid", "exists:specialties,id"],
        ];
    }
}
