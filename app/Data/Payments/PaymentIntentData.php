<?php

namespace App\Data\Payments;

class PaymentIntentData
{
    public function __construct(
        public readonly string $intentionId,
        public readonly string $orderId,
        public readonly string $clientSecret,
        public readonly string $checkoutUrl,
    ) {}
}
