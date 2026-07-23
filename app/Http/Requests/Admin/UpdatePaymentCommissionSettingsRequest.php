<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentCommissionSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('settings.update') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'card_commission_percentage' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            'cash_commission_percentage' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            '*.required' => 'نسبة العمولة مطلوبة.',
            '*.numeric' => 'نسبة العمولة يجب أن تكون رقمًا.',
            '*.decimal' => 'استخدم منزلتين عشريتين بحد أقصى.',
            '*.min' => 'نسبة العمولة لا يمكن أن تقل عن صفر.',
            '*.max' => 'نسبة العمولة لا يمكن أن تتجاوز 100%.',
        ];
    }
}
