<?php

namespace App\Http\Controllers\Api\Payment;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\CheckoutBookingRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Services\Payments\CheckoutService;
use Illuminate\Http\JsonResponse;

class BookingCheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout) {}

    public function __invoke(CheckoutBookingRequest $request, Booking $booking): JsonResponse
    {
        $payment = $this->checkout->checkout(
            $booking,
            $request->user('patient'),
            PaymentMethod::from($request->validated('method')),
            $request->validated('idempotency_key'),
        );
        $data = (new PaymentResource($payment))->resolve($request);

        if ($payment->method === PaymentMethod::Card && $payment->provider_client_secret) {
            $data['client_secret'] = $payment->provider_client_secret;
        }

        return response()->json(['data' => $data]);
    }
}
