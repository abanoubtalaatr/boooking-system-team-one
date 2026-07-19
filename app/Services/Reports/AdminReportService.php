<?php

namespace App\Services\Reports;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminReportService
{
    /** @var list<string> */
    private const SETTLED_PAYMENT_STATUSES = [
        PaymentStatus::Succeeded->value,
        PaymentStatus::Paid->value,
        PaymentStatus::CashCollected->value,
    ];

    /**
     * @return array{
     *     period: array{key: string, label: string, range: string, bucket_count: int, unit: string},
     *     summary: array{bookings: int, average: float, trend: ?float, paid_bookings: int, profit_cents: int},
     *     bookings: list<array{key: string, label: string, value: int}>,
     *     payments: list<array{key: string, label: string, card: int, cash: int}>,
     *     profits: list<array{key: string, label: string, value: float}>
     * }
     */
    public function build(string $period): array
    {
        $definition = $this->periodDefinition($period);
        $buckets = $this->buckets($definition['start'], $definition['count'], $definition['unit']);

        $bookingTotals = $this->bookingTotals($definition['start'], $definition['end'], $definition['unit']);
        $paymentTotals = $this->paymentTotals($definition['start'], $definition['end'], $definition['unit']);
        $currentBookings = (int) $bookingTotals->sum();
        $previousBookings = $this->previousPeriodBookings($definition['start'], $definition['count'], $definition['unit']);

        $bookingSeries = [];
        $paymentSeries = [];
        $profitSeries = [];

        foreach ($buckets as $bucket) {
            $payment = $paymentTotals->get($bucket['key']);
            $cardBookings = (int) ($payment->card_total ?? 0);
            $cashBookings = (int) ($payment->cash_total ?? 0);

            $bookingSeries[] = [
                'key' => $bucket['key'],
                'label' => $bucket['label'],
                'value' => (int) $bookingTotals->get($bucket['key'], 0),
            ];
            $paymentSeries[] = [
                'key' => $bucket['key'],
                'label' => $bucket['label'],
                'card' => $cardBookings,
                'cash' => $cashBookings,
            ];
            $profitSeries[] = [
                'key' => $bucket['key'],
                'label' => $bucket['label'],
                'value' => round(((int) ($payment->profit_cents ?? 0)) / 100, 2),
            ];
        }

        $profitCents = (int) $paymentTotals->sum('profit_cents');
        $paidBookings = (int) $paymentTotals->sum(
            fn (object $item): int => (int) $item->card_total + (int) $item->cash_total
        );

        return [
            'period' => [
                'key' => $period,
                'label' => $definition['label'],
                'range' => $definition['start']->format('d/m/Y').' - '.$definition['end']->format('d/m/Y'),
                'bucket_count' => $definition['count'],
                'unit' => $definition['unit'],
            ],
            'summary' => [
                'bookings' => $currentBookings,
                'average' => round($currentBookings / $definition['count'], 1),
                'trend' => $this->percentageChange($currentBookings, $previousBookings),
                'paid_bookings' => $paidBookings,
                'profit_cents' => $profitCents,
            ],
            'bookings' => $bookingSeries,
            'payments' => $paymentSeries,
            'profits' => $profitSeries,
        ];
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable, count: int, unit: string, label: string}
     */
    private function periodDefinition(string $period): array
    {
        $today = CarbonImmutable::today();

        return match ($period) {
            'forty_days' => [
                'start' => $today->subDays(39)->startOfDay(),
                'end' => $today->endOfDay(),
                'count' => 40,
                'unit' => 'day',
                'label' => 'آخر 40 يومًا',
            ],
            'year' => [
                'start' => $today->startOfMonth()->subMonths(11),
                'end' => $today->endOfMonth(),
                'count' => 12,
                'unit' => 'month',
                'label' => 'آخر 12 شهرًا',
            ],
            default => [
                'start' => $today->subDays(6)->startOfDay(),
                'end' => $today->endOfDay(),
                'count' => 7,
                'unit' => 'day',
                'label' => 'آخر 7 أيام',
            ],
        };
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    private function buckets(CarbonImmutable $start, int $count, string $unit): array
    {
        $months = [1 => 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
        $buckets = [];

        for ($index = 0; $index < $count; $index++) {
            $date = $unit === 'month' ? $start->addMonths($index) : $start->addDays($index);
            $buckets[] = [
                'key' => $date->format($unit === 'month' ? 'Y-m' : 'Y-m-d'),
                'label' => $unit === 'month'
                    ? $months[$date->month].' '.$date->format('Y')
                    : $date->format('d/m'),
            ];
        }

        return $buckets;
    }

    /** @return Collection<string, int> */
    private function bookingTotals(CarbonImmutable $start, CarbonImmutable $end, string $unit): Collection
    {
        $expression = $this->bucketExpression((new Booking)->qualifyColumn('created_at'), $unit);

        return Booking::query()
            ->toBase()
            ->selectRaw("{$expression} AS bucket, COUNT(*) AS total")
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw($expression)
            ->pluck('total', 'bucket');
    }

    /** @return Collection<string, object> */
    private function paymentTotals(CarbonImmutable $start, CarbonImmutable $end, string $unit): Collection
    {
        $expression = $this->bucketExpression((new Payment)->qualifyColumn('created_at'), $unit);
        $settledPlaceholders = implode(', ', array_fill(0, count(self::SETTLED_PAYMENT_STATUSES), '?'));

        $bindings = [
            PaymentMethod::Card->value,
            ...self::SETTLED_PAYMENT_STATUSES,
            PaymentMethod::Cash->value,
            ...self::SETTLED_PAYMENT_STATUSES,
            ...self::SETTLED_PAYMENT_STATUSES,
        ];

        return Payment::query()
            ->toBase()
            ->selectRaw("{$expression} AS bucket")
            ->selectRaw(
                "COUNT(DISTINCT CASE WHEN method = ? AND status IN ({$settledPlaceholders}) THEN booking_id END) AS card_total",
                array_slice($bindings, 0, 4)
            )
            ->selectRaw(
                "COUNT(DISTINCT CASE WHEN method = ? AND status IN ({$settledPlaceholders}) THEN booking_id END) AS cash_total",
                array_slice($bindings, 4, 4)
            )
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN status IN ({$settledPlaceholders}) THEN commission_amount_cents ELSE 0 END), 0) AS profit_cents",
                array_slice($bindings, 8)
            )
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw($expression)
            ->get()
            ->keyBy('bucket');
    }

    private function previousPeriodBookings(CarbonImmutable $start, int $count, string $unit): int
    {
        $previousStart = $unit === 'month' ? $start->subMonths($count) : $start->subDays($count);
        $previousEnd = $start->subSecond();

        return Booking::query()->whereBetween('created_at', [$previousStart, $previousEnd])->count();
    }

    private function percentageChange(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return $current === 0 ? 0.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function bucketExpression(string $column, string $unit): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return $unit === 'month'
                ? "strftime('%Y-%m', {$column})"
                : "strftime('%Y-%m-%d', {$column})";
        }

        return $unit === 'month'
            ? "DATE_FORMAT({$column}, '%Y-%m')"
            : "DATE_FORMAT({$column}, '%Y-%m-%d')";
    }
}
