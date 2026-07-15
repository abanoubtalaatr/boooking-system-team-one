<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Data\Payments\CreatePaymentIntentData;
use App\Data\Payments\PaymentIntentData;
use App\Data\Payments\RefundResultData;
use App\Exceptions\PaymentDomainException;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class PaymobGateway implements PaymentGatewayInterface
{
    private const HMAC_FIELDS = [
        'amount_cents', 'created_at', 'currency', 'error_occured', 'has_parent_transaction',
        'id', 'integration_id', 'is_3d_secure', 'is_auth', 'is_capture', 'is_refunded',
        'is_standalone_payment', 'is_voided', 'order.id', 'owner', 'pending',
        'source_data.pan', 'source_data.sub_type', 'source_data.type', 'success',
    ];

    public function createIntent(CreatePaymentIntentData $data): PaymentIntentData
    {
        $this->assertConfigured(['secret_key', 'public_key', 'card_integration_id', 'notification_url']);
        [$firstName, $lastName] = $this->splitName($data->patientName);

        $response = $this->request(retry: false)
            ->withToken((string) config('services.paymob.secret_key'), 'Token')
            ->post('/v1/intention/', [
                'amount' => $data->amountCents,
                'currency' => $data->currency,
                'payment_methods' => [(int) config('services.paymob.card_integration_id')],
                'items' => [],
                'billing_data' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $data->patientEmail,
                    'phone_number' => $data->patientPhone,
                    'apartment' => 'NA', 'floor' => 'NA', 'street' => 'NA', 'building' => 'NA',
                    'shipping_method' => 'NA', 'postal_code' => 'NA', 'city' => 'Cairo',
                    'country' => 'EG', 'state' => 'Cairo',
                ],
                'special_reference' => $data->reference,
                'notification_url' => config('services.paymob.notification_url'),
                'redirection_url' => config('services.paymob.redirection_url'),
            ]);

        if ($response->serverError() || $response->status() === 429) {
            throw new \RuntimeException('Paymob intention outcome is unknown.');
        }

        if (! $response->successful()) {
            throw new PaymentDomainException('تعذر بدء عملية الدفع حالياً. حاول مرة أخرى.', 'paymob_intention_failed', 502);
        }

        $clientSecret = (string) $response->json('client_secret');
        $intentionId = (string) $response->json('id');
        $orderId = (string) ($response->json('intention_order_id') ?? $response->json('order.id') ?? '');

        if ($clientSecret === '' || $intentionId === '') {
            throw new PaymentDomainException('استجابة بوابة الدفع غير مكتملة.', 'invalid_paymob_response', 502);
        }

        $checkoutUrl = rtrim((string) config('services.paymob.checkout_url'), '/').'/?'.http_build_query([
            'publicKey' => config('services.paymob.public_key'),
            'clientSecret' => $clientSecret,
        ]);

        return new PaymentIntentData($intentionId, $orderId, $clientSecret, $checkoutUrl);
    }

    public function refund(Payment $payment, int $amountCents): RefundResultData
    {
        $this->assertConfigured(['api_key']);

        try {
            $authResponse = $this->request()->post('/api/auth/tokens', [
                'api_key' => config('services.paymob.api_key'),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return new RefundResultData(false, failureMessage: 'Paymob authentication failed.');
        }

        if (! $authResponse->successful() || ! $authResponse->json('token')) {
            return new RefundResultData(false, failureMessage: 'Paymob authentication failed.');
        }

        try {
            $response = $this->request(retry: false)->post('/api/acceptance/void_refund/refund', [
                'auth_token' => $authResponse->json('token'),
                'transaction_id' => $payment->provider_transaction_id,
                'amount_cents' => $amountCents,
            ]);

            if ($response->serverError() || $response->status() === 429) {
                return new RefundResultData(
                    false,
                    failureMessage: 'Paymob refund outcome is pending verification.',
                    outcomeUnknown: true,
                );
            }

            if (! $response->successful() || $response->json('success') === false) {
                return new RefundResultData(false, failureMessage: (string) ($response->json('message') ?? 'Paymob refund failed.'));
            }

            $providerRefundId = $response->json('id') ?? $response->json('transaction_id');

            return new RefundResultData(
                true,
                filled($providerRefundId) ? (string) $providerRefundId : null,
            );
        } catch (Throwable $exception) {
            report($exception);

            return new RefundResultData(
                false,
                failureMessage: 'Paymob refund outcome is pending verification.',
                outcomeUnknown: true,
            );
        }
    }

    public function hasValidHmac(array $payload, ?string $providedHmac): bool
    {
        $secret = (string) config('services.paymob.hmac_secret');

        if ($secret === '' || blank($providedHmac)) {
            return false;
        }

        $concatenated = collect(self::HMAC_FIELDS)
            ->map(fn (string $field): string => $this->stringifyHmacValue(Arr::get($payload, $field, '')))
            ->implode('');

        return hash_equals(hash_hmac('sha512', $concatenated, $secret), (string) $providedHmac);
    }

    private function stringifyHmacValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }

    private function request(bool $retry = true): PendingRequest
    {
        $request = Http::baseUrl(rtrim((string) config('services.paymob.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->connectTimeout((int) config('services.paymob.connect_timeout', 3))
            ->timeout((int) config('services.paymob.timeout', 10));

        if (! $retry) {
            return $request;
        }

        return $request->retry([200, 500], when: function (Throwable $exception): bool {
            return $exception instanceof ConnectionException
                || ($exception instanceof RequestException && $exception->response->serverError());
        }, throw: false);
    }

    /** @param list<string> $keys */
    private function assertConfigured(array $keys): void
    {
        foreach ($keys as $key) {
            if (blank(config("services.paymob.{$key}"))) {
                throw new PaymentDomainException('إعدادات بوابة الدفع غير مكتملة.', 'paymob_not_configured', 503);
            }
        }
    }

    /** @return array{0: string, 1: string} */
    private function splitName(string $name): array
    {
        $parts = Str::of($name)->squish()->explode(' ');
        $firstName = (string) ($parts->shift() ?: 'Patient');
        $lastName = (string) ($parts->implode(' ') ?: $firstName);

        return [$firstName, $lastName];
    }
}
