<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpecializationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $specialization = $this->route('specialization');
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('specializations', 'name')->ignore($specialization?->id),
            ],

            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,svg',
                'max:2048',
            ],
        ];
    }
}
