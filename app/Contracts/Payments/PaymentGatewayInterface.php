<?php

namespace App\Contracts\Payments;

use App\Data\Payments\CreatePaymentIntentData;
use App\Data\Payments\PaymentIntentData;
use App\Data\Payments\RefundResultData;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    public function createIntent(CreatePaymentIntentData $data): PaymentIntentData;

    public function refund(Payment $payment, int $amountCents): RefundResultData;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function hasValidHmac(array $payload, ?string $providedHmac): bool;
}
