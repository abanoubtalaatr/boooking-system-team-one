<?php

namespace App\Data\Payments;

class CreatePaymentIntentData
{
    public function __construct(
        public readonly string $reference,
        public readonly int $amountCents,
        public readonly string $currency,
        public readonly string $patientName,
        public readonly string $patientEmail,
        public readonly string $patientPhone,
    ) {}
}
