<?php

namespace App\Http\Controllers\Api\Payment;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PaymobWebhookRequest;
use App\Services\Payments\PaymobWebhookService;
use Illuminate\Http\JsonResponse;

class PaymobWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
        private readonly PaymobWebhookService $webhooks,
    ) {}

    public function __invoke(PaymobWebhookRequest $request): JsonResponse
    {
        $payload = $request->transactionPayload();

        if (! $this->gateway->hasValidHmac($payload, $request->suppliedHmac())) {
            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        $this->webhooks->handle($payload);

        return response()->json(['message' => 'Webhook processed.']);
    }
}
