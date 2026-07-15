@php
    use App\Enums\WalletWithdrawalStatus;

    $statusLabels = [
        WalletWithdrawalStatus::PendingReview->value => ['قيد المراجعة', 'warning'],
        WalletWithdrawalStatus::Completed->value => ['مكتمل', 'success'],
        WalletWithdrawalStatus::Cancelled->value => ['ملغي', 'danger'],
    ];
@endphp

<x-layouts.dashboard title="المحفظة وطلبات السحب" role="doctor">
    <div class="breadcrumb">الرئيسية / المحفظة</div>
    <div class="page-head">
        <div>
            <h1 class="page-title">محفظتي</h1>
            <p class="page-description">تابع رصيدك المتاح، وأنشئ طلب سحب، وراجع حالة الطلبات السابقة.</p>
        </div>
        <span class="dashboard-live-indicator"><i></i> الرصيد محدث من عمليات الدفع</span>
    </div>

    @if (session('success'))
        <div class="settings-alert settings-alert--success">{{ session('success') }}</div>
    @endif

    <section class="stats" aria-label="ملخص المحفظة">
        @foreach ([
            ['متاح للسحب', $available_cents, 'بعد حجز الطلبات قيد المراجعة', 'report'],
            ['رصيد المحفظة', $balance_cents, 'إجمالي الرصيد الحالي', 'calendar'],
            ['قيد المراجعة', $pending_cents, 'لم يُخصم من الرصيد بعد', 'clock'],
            ['تم سحبه', $completed_cents, 'إجمالي الطلبات المكتملة', 'shield'],
        ] as [$label, $value, $hint, $icon])
            <article class="stat">
                <div class="stat-top">
                    <div>
                        <span class="stat-label">{{ $label }}</span>
                        <div class="stat-value stat-value--money">{{ number_format($value / 100, 2) }} {{ $wallet->currency }}</div>
                        <span class="stat-change">{{ $hint }}</span>
                    </div>
                    <span class="stat-icon"><x-ui-icon :name="$icon" /></span>
                </div>
            </article>
        @endforeach
    </section>

    <section class="wallet-layout">
        <article class="panel withdrawal-request-panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">طلب سحب جديد</h2>
                    <p class="panel-description">سيظل المبلغ محجوزًا حتى تراجع الإدارة الطلب.</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="filter-errors">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <form class="withdrawal-form" method="POST" action="{{ route('web.doctor.wallet.withdrawals.store') }}">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ $withdrawalRequestKey }}">
                <label class="filter-field">
                    المبلغ المطلوب ({{ $wallet->currency }})
                    <input
                        type="number"
                        name="amount"
                        value="{{ old('amount') }}"
                        min="1"
                        max="{{ number_format($available_cents / 100, 2, '.', '') }}"
                        step="0.01"
                        inputmode="decimal"
                        placeholder="0.00"
                        @disabled($available_cents < 100)
                        required
                    >
                </label>
                <p class="withdrawal-help">الحد الأقصى المتاح: <strong>{{ number_format($available_cents / 100, 2) }} {{ $wallet->currency }}</strong></p>
                <button class="primary-button" type="submit" @disabled($available_cents < 100)>
                    {{ $available_cents < 100 ? 'لا يوجد رصيد متاح' : 'إرسال طلب السحب' }}
                </button>
            </form>
        </article>

        <article class="panel withdrawal-policy">
            <h2 class="panel-title">كيف تتم المراجعة؟</h2>
            <ol>
                <li><strong>قيد المراجعة:</strong> يتم حجز المبلغ من المتاح دون خصمه من المحفظة.</li>
                <li><strong>مكتمل:</strong> وافقت الإدارة وتم خصم المبلغ نهائيًا.</li>
                <li><strong>ملغي:</strong> رُفض الطلب وأصبح المبلغ متاحًا للسحب مرة أخرى.</li>
            </ol>
        </article>
    </section>

    <section class="panel payment-panel">
        <div class="panel-head payment-panel-head">
            <div><h2 class="panel-title">سجل طلبات السحب</h2><p>جميع طلبات السحب الخاصة بمحفظتك فقط.</p></div>
            <span class="results-count">{{ number_format($withdrawals->total()) }} طلب</span>
        </div>
        <div class="payment-table-wrap">
            <table class="payment-table withdrawal-table">
                <thead><tr><th>رقم الطلب</th><th>المبلغ</th><th>الحالة</th><th>تاريخ الطلب</th><th>المراجعة</th><th>ملاحظات</th></tr></thead>
                <tbody>
                @forelse ($withdrawals as $withdrawal)
                    @php([$statusLabel, $statusTone] = $statusLabels[$withdrawal->status->value])
                    <tr>
                        <td><strong>#{{ str($withdrawal->uuid)->before('-') }}</strong></td>
                        <td class="money-cell">{{ number_format($withdrawal->amount_cents / 100, 2) }} <small>{{ $withdrawal->currency }}</small></td>
                        <td><span class="payment-status payment-status--{{ $statusTone }}">{{ $statusLabel }}</span></td>
                        <td>{{ $withdrawal->created_at->format('Y-m-d') }}<small>{{ $withdrawal->created_at->format('h:i A') }}</small></td>
                        <td>{{ $withdrawal->reviewer?->name ?? 'لم تتم المراجعة' }}@if($withdrawal->reviewed_at)<small>{{ $withdrawal->reviewed_at->format('Y-m-d h:i A') }}</small>@endif</td>
                        <td>{{ $withdrawal->rejection_reason ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td class="empty-payments" colspan="6">لا توجد طلبات سحب حتى الآن.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <x-payment-dashboard.pagination :paginator="$withdrawals" />
    </section>
</x-layouts.dashboard>
