<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $commissionBasisPoints = (int) $this->commission_bps;

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'booking' => [
                'id' => $this->booking->id,
                'number' => $this->booking->booking_number,
                'date' => $this->booking->booking_date?->toDateString(),
                'time' => $this->booking->booking_time?->format('H:i'),
                'consultation_type' => $this->booking->consultation_type,
                'status' => $this->booking->status,
                'payment_status' => $this->booking->payment_status,
            ],
            'doctor' => [
                'id' => $this->doctor->id,
                'name' => $this->doctor->name,
                'email' => $this->doctor->email,
            ],
            'patient' => [
                'id' => $this->patient->id,
                'name' => $this->patient->name,
                'email' => $this->patient->email,
                'phone' => $this->patient->phone,
            ],
            'method' => $this->method,
            'status' => $this->status,
            'gross_amount_cents' => (int) $this->amount_cents,
            'commission_bps' => $commissionBasisPoints,
            'commission_percentage' => sprintf('%d.%02d', intdiv($commissionBasisPoints, 100), $commissionBasisPoints % 100),
            'platform_fee_cents' => (int) $this->commission_amount_cents,
            'doctor_net_cents' => (int) $this->doctor_amount_cents,
            'currency' => $this->currency,
            'provider' => [
                'name' => $this->provider,
                'intention_id' => $this->provider_intention_id,
                'order_id' => $this->provider_order_id,
                'transaction_id' => $this->provider_transaction_id,
            ],
            'failure' => $this->failure_message ? [
                'code' => $this->failure_code,
                'message' => $this->failure_message,
            ] : null,
            'paid_at' => $this->paid_at?->toISOString(),
            'failed_at' => $this->failed_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'refunded_at' => $this->refunded_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
