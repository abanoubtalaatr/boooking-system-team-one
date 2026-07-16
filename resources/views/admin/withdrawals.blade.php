@php
    use App\Enums\WalletWithdrawalStatus;

    $statusLabels = [
        WalletWithdrawalStatus::PendingReview->value => ['قيد المراجعة', 'warning'],
        WalletWithdrawalStatus::Completed->value => ['مكتمل', 'success'],
        WalletWithdrawalStatus::Cancelled->value => ['ملغي', 'danger'],
    ];
@endphp

<x-layouts.dashboard title="إدارة طلبات السحب" role="admin">
    <div class="breadcrumb">الرئيسية / طلبات السحب</div>
    <div class="page-head">
        <div>
            <h1 class="page-title">طلبات سحب الأطباء</h1>
            <p class="page-description">راجع كل الطلبات، ثم اقبل الطلب لخصم الرصيد أو ارفضه مع توضيح السبب.</p>
        </div>
        <span class="dashboard-live-indicator"><i></i> إدارة مركزية للطلبات</span>
    </div>

    @if (session('success'))
        <div class="settings-alert settings-alert--success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="filter-errors withdrawal-errors">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <section class="stats" aria-label="ملخص طلبات السحب">
        @foreach ([
            ['طلبات قيد المراجعة', number_format($summary['pending_count']), 'تحتاج قرار الإدارة', 'clock'],
            ['مبالغ معلقة', number_format($summary['pending_cents'] / 100, 2).' EGP', 'محجوزة من المتاح', 'report'],
            ['سحوبات مكتملة', number_format($summary['completed_cents'] / 100, 2).' EGP', 'خُصمت من المحافظ', 'shield'],
            ['طلبات ملغاة', number_format($summary['cancelled_count']), 'دون خصم الرصيد', 'calendar'],
        ] as [$label, $value, $hint, $icon])
            <article class="stat"><div class="stat-top"><div><span class="stat-label">{{ $label }}</span><div class="stat-value stat-value--money">{{ $value }}</div><span class="stat-change">{{ $hint }}</span></div><span class="stat-icon"><x-ui-icon :name="$icon" /></span></div></article>
        @endforeach
    </section>

    <section class="panel payment-panel">
        <div class="panel-head payment-panel-head">
            <div><h2 class="panel-title">كل طلبات السحب</h2><p>يمكن البحث بالطبيب أو حالة الطلب.</p></div>
            <span class="results-count">{{ number_format($withdrawals->total()) }} نتيجة</span>
        </div>

        <form class="withdrawal-filters" method="GET" action="{{ route('web.admin.withdrawals.index') }}">
            <label class="filter-field">الحالة
                <select name="status">
                    <option value="">كل الحالات</option>
                    @foreach ($statusLabels as $value => [$label])
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="filter-field">الطبيب
                <select name="doctor_id">
                    <option value="">كل الأطباء</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((string) request('doctor_id') === (string) $doctor->id)>{{ $doctor->name }} — {{ $doctor->email }}</option>
                    @endforeach
                </select>
            </label>
            <div class="filter-actions"><button class="primary-button" type="submit">تطبيق</button><a class="secondary-button" href="{{ route('web.admin.withdrawals.index') }}">مسح</a></div>
        </form>

        <div class="payment-table-wrap">
            <table class="payment-table withdrawal-admin-table">
                <thead><tr><th>الطبيب</th><th>المبلغ</th><th>الحالة</th><th>تاريخ الطلب</th><th>تمت المراجعة بواسطة</th><th>الإجراء / السبب</th></tr></thead>
                <tbody>
                @forelse ($withdrawals as $withdrawal)
                    @php([$statusLabel, $statusTone] = $statusLabels[$withdrawal->status->value])
                    <tr>
                        <td><strong>{{ $withdrawal->doctor->name }}</strong><small>{{ $withdrawal->doctor->email }}</small></td>
                        <td class="money-cell">{{ number_format($withdrawal->amount_cents / 100, 2) }} <small>{{ $withdrawal->currency }}</small></td>
                        <td><span class="payment-status payment-status--{{ $statusTone }}">{{ $statusLabel }}</span></td>
                        <td>{{ $withdrawal->created_at->format('Y-m-d') }}<small>#{{ str($withdrawal->uuid)->before('-') }}</small></td>
                        <td>{{ $withdrawal->reviewer?->name ?? '—' }}@if($withdrawal->reviewed_at)<small>{{ $withdrawal->reviewed_at->format('Y-m-d h:i A') }}</small>@endif</td>
                        <td>
                            @if ($withdrawal->status === WalletWithdrawalStatus::PendingReview)
                                <div class="withdrawal-actions">
                                    @can('withdrawals.complete')<form method="POST" action="{{ route('web.admin.withdrawals.complete', $withdrawal) }}">
                                        @csrf @method('PATCH')
                                        <button class="withdrawal-button withdrawal-button--approve" type="submit">قبول وخصم</button>
                                    </form>@endcan
                                    @can('withdrawals.cancel')<form class="withdrawal-cancel-form" method="POST" action="{{ route('web.admin.withdrawals.cancel', $withdrawal) }}">
                                        @csrf @method('PATCH')
                                        <input name="rejection_reason" type="text" minlength="3" maxlength="500" placeholder="سبب الرفض" required>
                                        <button class="withdrawal-button withdrawal-button--cancel" type="submit">رفض</button>
                                    </form>@endcan
                                </div>
                            @else
                                {{ $withdrawal->rejection_reason ?? ($withdrawal->status === WalletWithdrawalStatus::Completed ? 'تم الخصم بنجاح' : '—') }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td class="empty-payments" colspan="6">لا توجد طلبات مطابقة.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <x-payment-dashboard.pagination :paginator="$withdrawals" />
    </section>
</x-layouts.dashboard>
