<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === "admin";
    }

    public function rules(): array
    {
        return [
            "bio" => ["sometimes", "nullable", "string"],
            "consultation_price" => ["sometimes", "nullable", "numeric", "min:0"],
            "is_approved" => ["sometimes", "boolean"],
        ];
    }
}
