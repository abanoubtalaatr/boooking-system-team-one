<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorDashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->resource;
        $doctor = $data['doctor'];
        $profile = $doctor->doctorProfile;

        return [
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'email' => $doctor->email,
                'specialty' => $profile?->specialty?->name,
                'hospital' => $profile?->hospital?->name,
                'consultation_price' => $profile?->price,
                'experience_years' => $profile?->experience_years,
                'is_active' => $profile?->is_active,
            ],
            'current_commission' => $data['current_commission'],
            'wallet' => $data['wallet'],
            'bookings' => $data['bookings'],
            'payments' => $data['payments'],
            'recent_bookings' => DoctorDashboardBookingResource::collection($data['recent_bookings']),
            'recent_wallet_transactions' => WalletTransactionResource::collection($data['recent_wallet_transactions']),
        ];
    }
}
