<?php

namespace App\Http\Requests\Wallet;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreWalletWithdrawalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Doctor;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:1'],
            'idempotency_key' => ['required', 'uuid'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'amount.required' => 'أدخل المبلغ المطلوب سحبه.',
            'amount.numeric' => 'مبلغ السحب يجب أن يكون رقمًا.',
            'amount.decimal' => 'استخدم منزلتين عشريتين بحد أقصى.',
            'amount.min' => 'الحد الأدنى لطلب السحب هو 1 EGP.',
        ];
    }
}
