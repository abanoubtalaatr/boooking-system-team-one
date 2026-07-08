<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHospitalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === "admin";
    }

    public function rules(): array
    {
        return [
            "name" => ["sometimes", "string", "max:255"],
            "latitude" => ["sometimes", "nullable", "numeric", "between:-90,90"],
            "longitude" => ["sometimes", "nullable", "numeric", "between:-180,180"],
        ];
    }
}
