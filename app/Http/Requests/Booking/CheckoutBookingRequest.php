<?php

namespace App\Http\Requests\Booking;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'idempotency_key' => ['required', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['idempotency_key' => $this->header('Idempotency-Key')]);
    }
}
