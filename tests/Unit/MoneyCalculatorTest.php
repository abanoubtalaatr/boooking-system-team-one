<?php

use App\Services\Payments\MoneyCalculator;

it('converts decimal money to cents using round half up', function (string $amount, int $expectedCents): void {
    expect((new MoneyCalculator)->decimalToCents($amount))->toBe($expectedCents);
})->with([
    ['500.00', 50000],
    ['10.004', 1000],
    ['10.005', 1001],
    ['0.01', 1],
]);

it('calculates zero and ten percent commission snapshots using integer round half up', function (): void {
    $money = new MoneyCalculator;

    expect($money->basisPointsAmount(105, 0))->toBe(0)
        ->and($money->basisPointsAmount(105, 1000))->toBe(11)
        ->and($money->basisPointsAmount(50000, 1000))->toBe(5000);
});
