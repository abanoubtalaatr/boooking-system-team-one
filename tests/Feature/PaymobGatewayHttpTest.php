<?php

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Data\Payments\CreatePaymentIntentData;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\SlotReservationStatus;
use App\Models\Patient;
use App\Models\Payment;
use App\Services\Payments\PaymobGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set([
        'services.paymob.base_url' => 'https://paymob.test',
        'services.paymob.secret_key' => 'test-secret-key',
        'services.paymob.public_key' => 'test-public-key',
        'services.paymob.api_key' => 'test-api-key',
        'services.paymob.card_integration_id' => 123456,
        'services.paymob.notification_url' => 'https://api.example.com/api/webhooks/paymob',
        'services.paymob.redirection_url' => 'https://api.example.com/api/payments/paymob/return',
        'services.paymob.checkout_url' => 'https://paymob.test/unifiedcheckout/',
        'services.paymob.timeout' => 2,
        'services.paymob.connect_timeout' => 1,
    ]);
    Http::preventStrayRequests();
});

function postCardCheckout($test, string $bookingKey, string $checkoutKey)
{
    [$doctor, $slot] = createBookableSlot();
    $patient = Patient::factory()->create();
    $bookingId = createBookingThroughApi($test, $patient, $doctor, $slot, $bookingKey);
    Sanctum::actingAs($patient, ['*'], 'patient');

    return [$test->withHeader('Idempotency-Key', $checkoutKey)
        ->postJson("/api/bookings/{$bookingId}/checkout", ['method' => 'card']), $bookingId, $slot];
}

it('creates an intention with server-owned money and returns Unified Checkout data', function (): void {
    Http::fake([
        'https://paymob.test/v1/intention/' => Http::response([
            'id' => 'intention-123',
            'intention_order_id' => 'order-123',
            'client_secret' => 'client-secret-123',
        ], 201),
    ]);

    $intent = app(PaymobGateway::class)->createIntent(new CreatePaymentIntentData(
        reference: 'payment-reference',
        amountCents: 50000,
        currency: 'EGP',
        patientName: 'Ahmed Ali',
        patientEmail: 'ahmed@example.com',
        patientPhone: '01012345678',
    ));

    expect($intent->clientSecret)->toBe('client-secret-123')
        ->and($intent->checkoutUrl)->toContain('publicKey=test-public-key')
        ->and($intent->checkoutUrl)->toContain('clientSecret=client-secret-123');
    Http::assertSent(fn (Request $request): bool => $request->hasHeader('Authorization', 'Token test-secret-key')
        && $request['amount'] === 50000
        && $request['special_reference'] === 'payment-reference'
        && $request['payment_methods'] === [123456]);
});

it('requests a full refund using the stored Paymob transaction id', function (): void {
    $payment = Payment::factory()->create(['provider_transaction_id' => 'transaction-to-refund']);
    Http::fake([
        'https://paymob.test/api/auth/tokens' => Http::response(['token' => 'auth-token'], 201),
        'https://paymob.test/api/acceptance/void_refund/refund' => Http::response(['id' => 'refund-transaction', 'success' => true]),
    ]);

    $result = app(PaymobGateway::class)->refund($payment, $payment->amount_cents);

    expect($result->succeeded)->toBeTrue()
        ->and($result->providerRefundId)->toBe('refund-transaction');
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/api/acceptance/void_refund/refund')
        && $request['transaction_id'] === 'transaction-to-refund'
        && $request['amount_cents'] === $payment->amount_cents);
});

it('does not retry a refund whose provider outcome is unknown', function (): void {
    $payment = Payment::factory()->create(['provider_transaction_id' => 'uncertain-refund-transaction']);
    Http::fake([
        'https://paymob.test/api/auth/tokens' => Http::response(['token' => 'auth-token'], 201),
        'https://paymob.test/api/acceptance/void_refund/refund' => Http::response(['detail' => 'temporary'], 500),
    ]);

    $result = app(PaymobGateway::class)->refund($payment, $payment->amount_cents);

    expect($result->succeeded)->toBeFalse()
        ->and($result->outcomeUnknown)->toBeTrue();
    Http::assertSentCount(2);
    expect(Http::recorded(fn (Request $request): bool => str_ends_with(
        $request->url(),
        '/api/acceptance/void_refund/refund',
    )))->toHaveCount(1);
});

it('keeps the booking held when Paymob has a transient server failure', function (): void {
    Http::fake(['*' => Http::response(['detail' => 'temporary'], 500)]);
    $this->app->bind(PaymentGatewayInterface::class, PaymobGateway::class);

    [$response, $bookingId, $slot] = postCardCheckout($this, 'server-error-booking', 'server-error-checkout');

    $response->assertSuccessful()->assertJsonPath('data.status', PaymentStatus::PendingVerification->value);
    expect(Payment::query()->where('booking_id', $bookingId)->value('status'))->toBe(PaymentStatus::PendingVerification)
        ->and($slot->fresh()->reservation_status)->toBe(SlotReservationStatus::Held);
    Http::assertSentCount(1);
});

it('keeps the booking held when the Paymob connection outcome is unknown', function (): void {
    Http::fake(['*' => Http::failedConnection()]);
    $this->app->bind(PaymentGatewayInterface::class, PaymobGateway::class);

    [$response, $bookingId, $slot] = postCardCheckout($this, 'timeout-booking', 'timeout-checkout');

    $response->assertSuccessful()->assertJsonPath('data.status', PaymentStatus::PendingVerification->value);
    expect(Payment::query()->where('booking_id', $bookingId)->value('status'))->toBe(PaymentStatus::PendingVerification)
        ->and($slot->fresh()->reservation_status)->toBe(SlotReservationStatus::Held);
    Http::assertSentCount(1);
});

it('fails safely and releases the slot after a definitive Paymob rejection', function (): void {
    Http::fake(['*' => Http::response(['detail' => 'invalid request'], 422)]);
    $this->app->bind(PaymentGatewayInterface::class, PaymobGateway::class);

    [$response, $bookingId, $slot] = postCardCheckout($this, 'rejected-intent-booking', 'rejected-intent-checkout');

    $response->assertStatus(502)->assertJsonPath('error.code', 'paymob_intention_failed');
    expect(Payment::query()->where('booking_id', $bookingId)->value('status'))->toBe(PaymentStatus::Failed)
        ->and($slot->fresh()->reservation_status)->toBe(SlotReservationStatus::Available)
        ->and($slot->fresh()->is_booked)->toBeFalse();
    Http::assertSentCount(1);
});

it('fails safely when Paymob returns a malformed successful response', function (): void {
    Http::fake(['*' => Http::response(['id' => 'missing-client-secret'], 201)]);
    $this->app->bind(PaymentGatewayInterface::class, PaymobGateway::class);

    [$response, $bookingId] = postCardCheckout($this, 'malformed-booking', 'malformed-checkout');

    $response->assertStatus(502)->assertJsonPath('error.code', 'invalid_paymob_response');
    expect(Payment::query()->where('booking_id', $bookingId)->value('status'))->toBe(PaymentStatus::Failed)
        ->and(Payment::query()->where('booking_id', $bookingId)->firstOrFail()->booking->status)->toBe(BookingStatus::PaymentFailed);
});
