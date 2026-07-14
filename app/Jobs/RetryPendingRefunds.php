<?php

namespace App\Jobs;

use App\Enums\RefundStatus;
use App\Models\PaymentRefund;
use App\Services\Payments\RefundService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class RetryPendingRefunds implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 240;

    public int $tries = 3;

    public int $timeout = 60;

    public array $backoff = [5, 30, 120];

    public function handle(RefundService $refunds): void
    {
        PaymentRefund::query()
            ->with('payment')
            ->where('status', RefundStatus::Failed)
            ->select(['id', 'payment_id', 'reason'])
            ->chunkById(50, function ($pendingRefunds) use ($refunds): void {
                foreach ($pendingRefunds as $pendingRefund) {
                    $refunds->refundFull($pendingRefund->payment, $pendingRefund->reason);
                }
            });
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Pending refund retry job failed.', [
            'error' => $exception?->getMessage(),
        ]);
    }
}
