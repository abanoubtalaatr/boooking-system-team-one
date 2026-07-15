@props(['payments', 'showDoctor' => false])

@php
    $statusLabels = [
        'pending' => 'قيد الانتظار', 'initiated' => 'بدأ الدفع', 'pending_verification' => 'بانتظار التحقق',
        'cash_due' => 'كاش مستحق', 'cash_collected' => 'تم تحصيل الكاش', 'succeeded' => 'ناجحة',
        'paid' => 'مدفوعة', 'failed' => 'فشلت', 'refund_pending' => 'استرداد قيد التنفيذ',
        'refunded' => 'تم الاسترداد', 'voided' => 'ملغاة',
    ];
    $statusClasses = [
        'succeeded' => 'success', 'paid' => 'success', 'cash_collected' => 'success',
        'failed' => 'danger', 'voided' => 'danger',
        'refunded' => 'neutral', 'refund_pending' => 'warning',
        'pending' => 'warning', 'initiated' => 'warning', 'pending_verification' => 'warning', 'cash_due' => 'warning',
    ];
@endphp

<div class="payment-table-wrap">
    <table class="payment-table">
        <thead>
        <tr>
            <th>الحجز</th>
            @if ($showDoctor)<th>الطبيب</th>@endif
            <th>المريض</th>
            <th>طريقة الدفع</th>
            <th>إجمالي الحجز</th>
            <th>عمولة المنصة</th>
            <th>صافي الطبيب</th>
            <th>الحالة</th>
            <th>التاريخ</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($payments as $payment)
            @php
                $status = $payment->status->value;
                $method = $payment->method->value;
            @endphp
            <tr>
                <td>
                    <strong>{{ $payment->booking->booking_number }}</strong>
                    <small>{{ $payment->uuid }}</small>
                </td>
                @if ($showDoctor)
                    <td><strong>{{ $payment->doctor->name }}</strong><small>{{ $payment->doctor->email }}</small></td>
                @endif
                <td><strong>{{ $payment->patient->name }}</strong><small>{{ $payment->patient->phone }}</small></td>
                <td><span class="payment-method payment-method--{{ $method }}">{{ $method === 'card' ? 'فيزا' : 'كاش' }}</span></td>
                <td class="money-cell">{{ number_format($payment->amount_cents / 100, 2) }} <small>{{ $payment->currency }}</small></td>
                <td class="money-cell fee-cell">{{ number_format($payment->commission_amount_cents / 100, 2) }} <small>{{ $payment->currency }}</small></td>
                <td class="money-cell net-cell">{{ number_format($payment->doctor_amount_cents / 100, 2) }} <small>{{ $payment->currency }}</small></td>
                <td><span class="payment-status payment-status--{{ $statusClasses[$status] ?? 'neutral' }}">{{ $statusLabels[$status] ?? $status }}</span></td>
                <td><strong>{{ $payment->created_at->format('Y-m-d') }}</strong><small>{{ $payment->created_at->format('H:i') }}</small></td>
            </tr>
        @empty
            <tr><td class="empty-payments" colspan="{{ $showDoctor ? 9 : 8 }}">لا توجد عمليات دفع مطابقة للفلاتر الحالية.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<x-payment-dashboard.pagination :paginator="$payments" />
