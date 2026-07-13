<?php

namespace App\Data\Payments;

class RefundResultData
{
    public function __construct(
        public readonly bool $succeeded,
        public readonly ?string $providerRefundId = null,
        public readonly ?string $failureMessage = null,
    ) {}
}
