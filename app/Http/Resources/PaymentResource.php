<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'booking_id' => $this->booking_id,
            'method' => $this->method,
            'status' => $this->status,
            'amount_cents' => $this->amount_cents,
            'gross_amount_cents' => $this->amount_cents,
            'currency' => $this->currency,
            'commission_amount_cents' => $this->commission_amount_cents,
            'platform_fee_cents' => $this->commission_amount_cents,
            'doctor_amount_cents' => $this->doctor_amount_cents,
            'doctor_net_cents' => $this->doctor_amount_cents,
            'checkout_url' => $this->when($this->method?->value === 'card', $this->checkout_url),
            'failure' => $this->when($this->failure_code !== null, [
                'code' => $this->failure_code,
                'message' => $this->failure_message,
            ]),
            'paid_at' => $this->paid_at,
            'expires_at' => $this->expires_at,
            'refunded_at' => $this->refunded_at,
            'created_at' => $this->created_at,
        ];
    }
}
