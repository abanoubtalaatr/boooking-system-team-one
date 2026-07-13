<?php

namespace App\Services\Payments;

use InvalidArgumentException;

class MoneyCalculator
{
    public function decimalToCents(string $amount): int
    {
        $normalized = trim($amount);

        if (preg_match('/^\d+(?:\.\d+)?$/', $normalized) !== 1) {
            throw new InvalidArgumentException('Money amount must be a positive decimal value.');
        }

        [$whole, $fraction] = array_pad(explode('.', $normalized, 2), 2, '');
        $threeDigits = str_pad(substr($fraction, 0, 3), 3, '0');
        $cents = ((int) $whole * 100) + (int) substr($threeDigits, 0, 2);

        if ((int) $threeDigits[2] >= 5) {
            $cents++;
        }

        return $cents;
    }

    public function basisPointsAmount(int $amountCents, int $basisPoints): int
    {
        if ($amountCents < 0 || $basisPoints < 0 || $basisPoints > 10000) {
            throw new InvalidArgumentException('Invalid amount or basis points.');
        }

        return intdiv(($amountCents * $basisPoints) + 5000, 10000);
    }
}
