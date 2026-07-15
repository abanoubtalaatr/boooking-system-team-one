<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount_cents' => $this->amount_cents,
            'balance_after_cents' => $this->balance_after_cents,
            'currency' => $this->wallet?->currency,
            'payment_id' => $this->payment?->uuid,
            'booking' => $this->booking ? [
                'id' => $this->booking->id,
                'booking_number' => $this->booking->booking_number,
            ] : null,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
        ];
    }
}
