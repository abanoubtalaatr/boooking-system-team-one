<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class PaymobWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    /** @return array<string, mixed> */
    public function transactionPayload(): array
    {
        $object = $this->input('obj');

        return is_array($object) ? $object : $this->except('hmac');
    }

    public function suppliedHmac(): ?string
    {
        return $this->query('hmac') ?? $this->input('hmac');
    }
}
