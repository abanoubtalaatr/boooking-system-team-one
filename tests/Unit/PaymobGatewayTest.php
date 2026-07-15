<?php

use App\Services\Payments\PaymobGateway;
use Tests\TestCase;

uses(TestCase::class);

it('validates the Paymob transaction HMAC using the documented field order', function (): void {
    config()->set('services.paymob.hmac_secret', 'test-hmac-secret');
    $payload = [
        'amount_cents' => 50000,
        'created_at' => '2026-07-13T10:00:00Z',
        'currency' => 'EGP',
        'error_occured' => false,
        'has_parent_transaction' => false,
        'id' => 12345,
        'integration_id' => 987,
        'is_3d_secure' => true,
        'is_auth' => false,
        'is_capture' => true,
        'is_refunded' => false,
        'is_standalone_payment' => true,
        'is_voided' => false,
        'order' => ['id' => 777],
        'owner' => 1,
        'pending' => false,
        'source_data' => ['pan' => '2345', 'sub_type' => 'MasterCard', 'type' => 'card'],
        'success' => true,
    ];
    $fields = [
        'amount_cents', 'created_at', 'currency', 'error_occured', 'has_parent_transaction',
        'id', 'integration_id', 'is_3d_secure', 'is_auth', 'is_capture', 'is_refunded',
        'is_standalone_payment', 'is_voided', 'order.id', 'owner', 'pending',
        'source_data.pan', 'source_data.sub_type', 'source_data.type', 'success',
    ];
    $concatenated = collect($fields)->map(function (string $field) use ($payload): string {
        $value = data_get($payload, $field, '');

        return is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
    })->implode('');
    $hmac = hash_hmac('sha512', $concatenated, 'test-hmac-secret');
    $gateway = new PaymobGateway;

    expect($gateway->hasValidHmac($payload, $hmac))->toBeTrue()
        ->and($gateway->hasValidHmac([...$payload, 'amount_cents' => 1], $hmac))->toBeFalse();
});
