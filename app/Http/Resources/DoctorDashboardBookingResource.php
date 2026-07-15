<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorDashboardBookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payment = $this->latestPayment;

        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'patient' => [
                'id' => $this->patient?->id,
                'name' => $this->patient?->name,
                'phone' => $this->patient?->phone,
                'email' => $this->patient?->email,
            ],
            'booking_date' => $this->booking_date?->toDateString(),
            'booking_time' => $this->booking_time?->format('H:i'),
            'consultation_type' => $this->consultation_type,
            'price' => $this->price,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'latest_payment' => $payment ? [
                'id' => $payment->uuid,
                'method' => $payment->method,
                'status' => $payment->status,
                'gross_amount_cents' => $payment->amount_cents,
                'platform_fee_cents' => $payment->commission_amount_cents,
                'doctor_net_cents' => $payment->doctor_amount_cents,
                'commission_bps' => $payment->commission_bps,
                'currency' => $payment->currency,
                'paid_at' => $payment->paid_at,
            ] : null,
            'created_at' => $this->created_at,
        ];
    }
}
