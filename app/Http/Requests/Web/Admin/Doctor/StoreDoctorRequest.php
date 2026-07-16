<?php

namespace App\Http\Requests\Web\Admin\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],

            'specialization_id' => ['required', 'exists:specializations,id'],
            'hospital_id'       => ['required', 'exists:hospitals,id'],
        ];
    }
}