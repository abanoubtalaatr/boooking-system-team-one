<?php

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Data\Payments\PaymentIntentData;
use App\Data\Payments\RefundResultData;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Enums\SlotReservationStatus;
use App\Jobs\ExpireBookingHolds;
use App\Jobs\RetryPendingRefunds;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentRefundedNotification;
use App\Notifications\PaymentSucceededNotification;
use App\Services\Payments\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function cardPaymentFixture($test): Payment
{
    [$doctor, $slot] = createBookableSlot();
    $patient = Patient::factory()->create();
    $gateway = Mockery::mock(PaymentGatewayInterface::class);
    $gateway->shouldReceive('createIntent')->once()->andReturn(new PaymentIntentData(
        'intent-webhook', 'order-webhook', 'secret-webhook', 'https://checkout.test/webhook',
    ));
    app()->instance(PaymentGatewayInterface::class, $gateway);
    $bookingId = createBookingThroughApi($test, $patient, $doctor, $slot, 'webhook-booking');
    Sanctum::actingAs($patient, ['*'], 'patient');
    $paymentId = $test->withHeader('Idempotency-Key', 'webhook-checkout')
        ->postJson("/api/bookings/{$bookingId}/checkout", ['method' => 'card'])
        ->assertSuccessful()
        ->json('data.id');

    return Payment::query()->where('uuid', $paymentId)->firstOrFail();
}

function paymobPayload(Payment $payment, bool $success = true): array
{
    return [
        'amount_cents' => $payment->amount_cents,
        'created_at' => now()->toISOString(),
        'currency' => $payment->currency,
        'error_occured' => ! $success,
        'has_parent_transaction' => false,
        'id' => 'transaction-'.$payment->id,
        'integration_id' => (int) config('services.paymob.card_integration_id'),
        'is_3d_secure' => true,
        'is_auth' => false,
        'is_capture' => true,
        'is_refunded' => false,
        'is_standalone_payment' => true,
        'is_voided' => false,
        'order' => ['id' => $payment->provider_order_id, 'merchant_order_id' => $payment->uuid],
        'owner' => 1,
        'pending' => false,
        'source_data' => ['pan' => '2345', 'sub_type' => 'MasterCard', 'type' => 'card'],
        'success' => $success,
        'special_reference' => $payment->uuid,
        'data' => ['message' => $success ? 'Approved' : 'Declined'],
    ];
}

it('confirms a successful Paymob callback and credits the wallet exactly once', function (): void {
    Notification::fake();
    $payment = cardPaymentFixture($this);
    $gateway = Mockery::mock(PaymentGatewayInterface::class);
    $gateway->shouldReceive('hasValidHmac')->twice()->andReturnTrue();
    $this->app->instance(PaymentGatewayInterface::class, $gateway);
    $payload = paymobPayload($payment);

    $this->postJson('/api/webhooks/paymob?hmac=valid', ['obj' => $payload])->assertSuccessful();
    $this->postJson('/api/webhooks/paymob?hmac=valid', ['obj' => $payload])->assertSuccessful();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Succeeded)
        ->and($payment->booking->fresh()->status)->toBe(BookingStatus::Confirmed)
        ->and($payment->booking->slot->fresh()->reservation_status)->toBe(SlotReservationStatus::Booked)
        ->and(Wallet::query()->where('doctor_id', $payment->doctor_id)->value('balance_cents'))->toBe($payment->doctor_amount_cents)
        ->and(WalletTransaction::query()->where('payment_id', $payment->id)->count())->toBe(1);
    Notification::assertSentTo($payment->patient, PaymentSucceededNotification::class);
    Notification::assertSentTo($payment->doctor, PaymentSucceededNotification::class);
});

it('keeps a confirmed booking intact when a later success uses a different transaction id', function (): void {
    Notification::fake();
    $payment = cardPaymentFixture($this);
    $gateway = Mockery::mock(PaymentGatewayInterface::class);
    $gateway->shouldReceive('hasValidHmac')->twice()->andReturnTrue();
    $this->app->instance(PaymentGatewayInterface::class, $gateway);
    $firstPayload = paymobPayload($payment);

    $this->postJson('/api/webhooks/paymob?hmac=valid', ['obj' => $firstPayload])->assertSuccessful();
    $secondPayload = $firstPayload;
    $secondPayload['id'] = 'different-transaction-'.$payment->id;
    $this->postJson('/api/webhooks/paymob?hmac=valid', ['obj' => $secondPayload])->assertSuccessful();

    expect($payment->fresh()->provider_transaction_id)->toBe($firstPayload['id'])
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Succeeded)
        ->and($payment->booking->fresh()->status)->toBe(BookingStatus::Confirmed)
        ->and($payment->booking->slot->fresh()->reservation_status)->toBe(SlotReservationStatus::Booked)
        ->and(Wallet::query()->where('doctor_id', $payment->doctor_id)->value('balance_cents'))->toBe($payment->doctor_amount_cents)
        ->and(WalletTransaction::query()->where('payment_id', $payment->id)->count())->toBe(1)
        ->and($payment->refunds()->count())->toBe(0);
});

it('releases the slot when Paymob rejects the payment', function (): void {
    Notification::fake();
    $payment = cardPaymentFixture($this);
    $gateway = Mockery::mock(PaymentGatewayInterface::class);
    $gateway->shouldReceive('hasValidHmac')->once()->andReturnTrue();
    $this->app->instance(PaymentGatewayInterface::class, $gateway);

    $this->postJson('/api/webhooks/paymob?hmac=valid', ['obj' => paymobPayload($payment, false)])
        ->assertSuccessful();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Failed)
        ->and($payment->booking->fresh()->status)->toBe(BookingStatus::PaymentFailed)
        ->and($payment->booking->slot->fresh()->reservation_status)->toBe(SlotReservationStatus::Available)
        ->and($payment->booking->slot->is_booked)->toBeFalse();
    Notification::assertSentTo($payment->patient, PaymentFailedNotification::class);
    Notification::assertSentTo($payment->doctor, PaymentFailedNotification::class);
});

it('rejects a callback with an invalid HMAC', function (): void {
    $payment = cardPaymentFixture($this);
    $gateway = Mockery::mock(PaymentGatewayInterface::class);
    $gateway->shouldReceive('hasValidHmac')->once()->andReturnFalse();
    $this->app->instance(PaymentGatewayInterface::class, $gateway);

    $this->postJson('/api/webhooks/paymob?hmac=invalid', ['obj' => paymobPayload($payment)])
        ->assertUnauthorized();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending);
});

it('automatically refunds a late successful callback after the hold expired', function (): void {
    Notification::fake();
    $payment = cardPaymentFixture($this);
    $payment->booking->update(['hold_expires_at' => now()->subMinute()]);
    (new ExpireBookingHolds)->handle();

    $gateway = Mockery::mock(PaymentGatewayInterface::class);
    $gateway->shouldReceive('hasValidHmac')->once()->andReturnTrue();
    $gateway->shouldReceive('refund')->once()->andReturn(new RefundResultData(true, 'refund-1'));
    $this->app->instance(PaymentGatewayInterface::class, $gateway);

    $this->postJson('/api/webhooks/paymob?hmac=valid', ['obj' => paymobPayload($payment)])
        ->assertSuccessful();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Refunded)
        ->and($payment->booking->fresh()->status)->toBe(BookingStatus::Refunded)
        ->and($payment->refunds()->count())->toBe(1)
        ->and($payment->booking->slot->fresh()->reservation_status)->toBe(SlotReservationStatus::Available);
    Notification::assertSentTo($payment->patient, PaymentRefundedNotification::class);
    Notification::assertSentTo($payment->doctor, PaymentRefundedNotification::class);
});

it('keeps the slot booked during cancellation refund and releases it after success', function (): void {
    Notification::fake();
    $payment = cardPaymentFixture($this);
    $webhookGateway = Mockery::mock(PaymentGatewayInterface::class);
    $webhookGateway->shouldReceive('hasValidHmac')->once()->andReturnTrue();
    $this->app->instance(PaymentGatewayInterface::class, $webhookGateway);
    $this->postJson('/api/webhooks/paymob?hmac=valid', ['obj' => paymobPayload($payment)])->assertSuccessful();

    $refundGateway = Mockery::mock(PaymentGatewayInterface::class);
    $refundGateway->shouldReceive('refund')->once()->andReturn(new RefundResultData(true, 'refund-cancel'));
    $this->app->instance(PaymentGatewayInterface::class, $refundGateway);
    Sanctum::actingAs($payment->patient, ['*'], 'patient');

    $this->putJson("/api/bookings/{$payment->booking_id}/cancel")
        ->assertSuccessful()
        ->assertJsonPath('data.status', BookingStatus::Refunded->value);

    expect($payment->fresh()->status)->toBe(PaymentStatus::Refunded)
        ->and($payment->fresh()->refunded_at)->not->toBeNull()
        ->and($payment->refunds()->firstOrFail()->amount_cents)->toBe($payment->amount_cents)
        ->and($payment->booking->slot->fresh()->reservation_status)->toBe(SlotReservationStatus::Available)
        ->and(Wallet::query()->where('doctor_id', $payment->doctor_id)->value('balance_cents'))->toBe(0)
        ->and(Wallet::query()->where('doctor_id', $payment->doctor_id)->firstOrFail()->payout_blocked)->toBeTrue()
        ->and(WalletTransaction::query()->where('payment_id', $payment->id)->count())->toBe(2);
});

it('rejects callbacks whose trusted transaction fields do not match the payment', function (string $field, mixed $value): void {
    $payment = cardPaymentFixture($this);
    $gateway = Mockery::mock(PaymentGatewayInterface::class);
    $gateway->shouldReceive('hasValidHmac')->once()->andReturnTrue();
    $this->app->instance(PaymentGatewayInterface::class, $gateway);
    $payload = paymobPayload($payment);
    data_set($payload, $field, $value);

    $this->postJson('/api/webhooks/paymob?hmac=valid', ['obj' => $payload])
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'webhook_data_mismatch');

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending)
        ->and($payment->booking->slot->fresh()->reservation_status)->toBe(SlotReservationStatus::Held);
})->with([
    'amount mismatch' => ['amount_cents', 1],
    'currency mismatch' => ['currency', 'USD'],
    'integration mismatch' => ['integration_id', 999999999],
    'missing transaction id' => ['id', ''],
]);

it('rejects a callback with an unknown payment reference', function (): void {
    $payment = cardPaymentFixture($this);
    $gateway = Mockery::mock(PaymentGatewayInterface::class);
    $gateway->shouldReceive('hasValidHmac')->once()->andReturnTrue();
    $this->app->instance(PaymentGatewayInterface::class, $gateway);
    $payload = paymobPayload($payment);
    $payload['special_reference'] = 'unknown-payment-reference';
    $payload['order']['merchant_order_id'] = 'unknown-payment-reference';

    $this->postJson('/api/webhooks/paymob?hmac=valid', ['obj' => $payload])
        ->assertNotFound();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending);
});

it('rejects a callback when every payment reference is missing', function (): void {
    $payment = cardPaymentFixture($this);
    $gateway = Mockery::mock(PaymentGatewayInterface::class);
    $gateway->shouldReceive('hasValidHmac')->once()->andReturnTrue();
    $this->app->instance(PaymentGatewayInterface::class, $gateway);
    $payload = paymobPayload($payment);
    unset($payload['special_reference'], $payload['order']['merchant_order_id']);
    $payload['order']['id'] = '';

    $this->postJson('/api/webhooks/paymob?hmac=valid', ['obj' => $payload])
        ->assertNotFound()
        ->assertJsonPath('error.code', 'payment_reference_not_found');

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending);
});

it('keeps the slot unavailable when refund fails and releases it after the scheduled retry succeeds', function (): void {
    Notification::fake();
    $payment = cardPaymentFixture($this);
    $webhookGateway = Mockery::mock(PaymentGatewayInterface::class);
    $webhookGateway->shouldReceive('hasValidHmac')->once()->andReturnTrue();
    $this->app->instance(PaymentGatewayInterface::class, $webhookGateway);
    $this->postJson('/api/webhooks/paymob?hmac=valid', ['obj' => paymobPayload($payment)])->assertSuccessful();

    $failedRefundGateway = Mockery::mock(PaymentGatewayInterface::class);
    $failedRefundGateway->shouldReceive('refund')->once()->andReturn(new RefundResultData(false, failureMessage: 'temporary failure'));
    $this->app->instance(PaymentGatewayInterface::class, $failedRefundGateway);
    Sanctum::actingAs($payment->patient, ['*'], 'patient');
    $this->putJson("/api/bookings/{$payment->booking_id}/cancel")
        ->assertSuccessful()
        ->assertJsonPath('data.status', BookingStatus::RefundPending->value);

    expect($payment->refunds()->firstOrFail()->status)->toBe(RefundStatus::Failed)
        ->and($payment->booking->slot->fresh()->reservation_status)->toBe(SlotReservationStatus::Booked);

    $successfulRefundGateway = Mockery::mock(PaymentGatewayInterface::class);
    $successfulRefundGateway->shouldReceive('refund')->once()->andReturn(new RefundResultData(true, 'retry-refund'));
    $this->app->instance(PaymentGatewayInterface::class, $successfulRefundGateway);
    (new RetryPendingRefunds)->handle(app(RefundService::class));

    expect($payment->fresh()->status)->toBe(PaymentStatus::Refunded)
        ->and($payment->refunds()->firstOrFail()->status)->toBe(RefundStatus::Succeeded)
        ->and($payment->booking->slot->fresh()->reservation_status)->toBe(SlotReservationStatus::Available);
});

it('holds an uncertain refund for reconciliation instead of submitting it again', function (): void {
    Notification::fake();
    $payment = cardPaymentFixture($this);
    $webhookGateway = Mockery::mock(PaymentGatewayInterface::class);
    $webhookGateway->shouldReceive('hasValidHmac')->once()->andReturnTrue();
    $this->app->instance(PaymentGatewayInterface::class, $webhookGateway);
    $this->postJson('/api/webhooks/paymob?hmac=valid', ['obj' => paymobPayload($payment)])->assertSuccessful();

    $uncertainRefundGateway = Mockery::mock(PaymentGatewayInterface::class);
    $uncertainRefundGateway->shouldReceive('refund')->once()->andReturn(new RefundResultData(
        false,
        failureMessage: 'provider outcome unknown',
        outcomeUnknown: true,
    ));
    $this->app->instance(PaymentGatewayInterface::class, $uncertainRefundGateway);
    Sanctum::actingAs($payment->patient, ['*'], 'patient');
    $this->putJson("/api/bookings/{$payment->booking_id}/cancel")
        ->assertSuccessful()
        ->assertJsonPath('data.status', BookingStatus::RefundPending->value);

    $retryGateway = Mockery::mock(PaymentGatewayInterface::class);
    $retryGateway->shouldNotReceive('refund');
    $this->app->instance(PaymentGatewayInterface::class, $retryGateway);
    (new RetryPendingRefunds)->handle(app(RefundService::class));

    expect($payment->refunds()->firstOrFail()->status)->toBe(RefundStatus::PendingVerification)
        ->and($payment->fresh()->status)->toBe(PaymentStatus::RefundPending)
        ->and($payment->booking->fresh()->status)->toBe(BookingStatus::RefundPending)
        ->and($payment->booking->slot->fresh()->reservation_status)->toBe(SlotReservationStatus::Booked);
});

it('does not blindly submit a pending refund left by an interrupted worker', function (): void {
    $payment = cardPaymentFixture($this);
    PaymentRefund::factory()->create([
        'payment_id' => $payment->id,
        'status' => RefundStatus::Pending,
    ]);
    $gateway = Mockery::mock(PaymentGatewayInterface::class);
    $gateway->shouldNotReceive('refund');
    $this->app->instance(PaymentGatewayInterface::class, $gateway);

    (new RetryPendingRefunds)->handle(app(RefundService::class));

    expect($payment->refunds()->firstOrFail()->status)->toBe(RefundStatus::Pending);
});
