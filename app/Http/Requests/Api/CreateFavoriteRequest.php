<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateFavoriteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'doctor_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('favorites', 'doctor_id')->where('user_id', $this->user('patient')->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.required' => 'The doctor id is required',
            'doctor_id.exists' => 'The doctor is not found',
            'doctor_id.unique' => 'The doctor is already in your favorites',
        ];
    }
}
