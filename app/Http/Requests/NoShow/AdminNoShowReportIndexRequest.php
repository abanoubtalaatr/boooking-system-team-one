<?php

namespace App\Http\Requests\NoShow;

use App\Enums\NoShowReportStatus;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminNoShowReportIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(NoShowReportStatus::class)],
            'doctor_id' => ['nullable', 'integer', 'exists:users,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
