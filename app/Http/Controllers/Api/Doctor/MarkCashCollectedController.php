<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Services\Payments\WalletService;
use Illuminate\Http\Request;

class MarkCashCollectedController extends Controller
{
    public function __construct(private readonly WalletService $wallets) {}

    public function __invoke(Request $request, Booking $booking): PaymentResource
    {
        return new PaymentResource(
            $this->wallets->markCashCollected($booking, (int) $request->user()->id),
        );
    }
}
