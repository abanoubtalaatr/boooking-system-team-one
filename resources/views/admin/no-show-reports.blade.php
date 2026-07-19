@php
    use App\Enums\NoShowReportStatus;

    $reportStatuses = [
        NoShowReportStatus::PendingReview->value => ['قيد المراجعة', 'warning'],
        NoShowReportStatus::Approved->value => ['مقبول', 'success'],
        NoShowReportStatus::Rejected->value => ['مرفوض', 'danger'],
    ];
@endphp

<x-layouts.dashboard title="مراجعة بلاغات عدم الحضور" role="admin">
    <div class="breadcrumb">الرئيسية / بلاغات عدم الحضور</div>
    <div class="page-head">
        <div>
            <h1 class="page-title">مراجعة بلاغات عدم الحضور</h1>
            <p class="page-description">تحقق من البلاغ قبل إلغاء الحجز ورد العمولة أو تنفيذ استرداد الدفع الإلكتروني.</p>
        </div>
        <span class="dashboard-live-indicator"><i></i> قرارات مالية موثقة</span>
    </div>

    @if (session('success'))
        <div class="settings-alert settings-alert--success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="filter-errors no-show-errors">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <section class="stats" aria-label="ملخص بلاغات عدم الحضور">
        @foreach ([
            ['كل البلاغات', $summary['total'], 'إجمالي البلاغات المسجلة', 'report'],
            ['تحتاج مراجعة', $summary['pending'], 'في انتظار قرار الإدارة', 'clock'],
            ['تم قبولها', $summary['approved'], 'أُلغيت أو استُردت ماليًا', 'shield'],
            ['تم رفضها', $summary['rejected'], 'بقي الحجز دون تغيير', 'calendar'],
        ] as [$label, $value, $hint, $icon])
            <article class="stat"><div class="stat-top"><div><span class="stat-label">{{ $label }}</span><div class="stat-value">{{ number_format($value) }}</div><span class="stat-change">{{ $hint }}</span></div><span class="stat-icon"><x-ui-icon :name="$icon" /></span></div></article>
        @endforeach
    </section>

    <section class="panel payment-panel no-show-panel">
        <div class="panel-head payment-panel-head">
            <div><h2 class="panel-title">كل البلاغات</h2><p>يمكن تصفية النتائج حسب الطبيب أو حالة المراجعة.</p></div>
            <span class="results-count">{{ number_format($reports->total()) }} نتيجة</span>
        </div>

        <form class="withdrawal-filters" method="GET" action="{{ route('web.admin.no-show-reports.index') }}">
            <label class="filter-field">الحالة
                <select name="status">
                    <option value="">كل الحالات</option>
                    @foreach ($reportStatuses as $value => [$label])
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
            <div class="filter-actions"><button class="primary-button" type="submit">تطبيق</button><a class="secondary-button" href="{{ route('web.admin.no-show-reports.index') }}">مسح</a></div>
        </form>

        <div class="payment-table-wrap">
            <table class="payment-table no-show-admin-table">
                <thead><tr><th>الطبيب</th><th>الحجز والمريض</th><th>سبب البلاغ</th><th>الدفع</th><th>الحالة</th><th>المراجعة</th></tr></thead>
                <tbody>
                @forelse ($reports as $report)
                    @php
                        [$statusLabel, $statusTone] = $reportStatuses[$report->status->value];
                        $payment = $report->booking->latestPayment;
                    @endphp
                    <tr>
                        <td><strong>{{ $report->doctor->name }}</strong><small>{{ $report->doctor->email }}</small></td>
                        <td><strong>{{ $report->booking->booking_number }}</strong><small>{{ $report->booking->patient->name }} · {{ $report->booking->booking_date->format('Y-m-d') }}</small></td>
                        <td class="no-show-reason">{{ $report->reason }}</td>
                        <td>
                            @if ($payment)
                                <strong>{{ $payment->method->value === 'cash' ? 'كاش' : 'فيزا' }}</strong>
                                <small>{{ $payment->method->value === 'cash' ? 'رد عمولة ' : 'استرداد كامل · عمولة ' }}{{ number_format($payment->commission_amount_cents / 100, 2) }} {{ $payment->currency }}</small>
                            @else
                                <strong>بدون دفع</strong>
                            @endif
                        </td>
                        <td><span class="payment-status payment-status--{{ $statusTone }}">{{ $statusLabel }}</span>@if($report->reviewer)<small>بواسطة {{ $report->reviewer->name }}</small>@endif</td>
                        <td>
                            @if ($report->status === NoShowReportStatus::PendingReview)
                                <div class="no-show-review-actions">
                                    @can('no-show-reports.approve')<form method="POST" action="{{ route('web.admin.no-show-reports.approve', $report) }}">
                                        @csrf @method('PATCH')
                                        <input name="review_note" type="text" maxlength="2000" placeholder="ملاحظة الموافقة (اختياري)">
                                        <button class="no-show-button no-show-button--approve" type="submit">قبول وتسوية</button>
                                    </form>@endcan
                                    @can('no-show-reports.reject')<form method="POST" action="{{ route('web.admin.no-show-reports.reject', $report) }}">
                                        @csrf @method('PATCH')
                                        <input name="review_note" type="text" maxlength="2000" placeholder="سبب الرفض" required>
                                        <button class="no-show-button no-show-button--reject" type="submit">رفض البلاغ</button>
                                    </form>@endcan
                                </div>
                            @else
                                {{ $report->review_note ?? 'تمت المراجعة دون ملاحظة' }}
                                @if($report->reviewed_at)<small>{{ $report->reviewed_at->format('Y-m-d H:i') }}</small>@endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td class="empty-payments" colspan="6">لا توجد بلاغات مطابقة للفلاتر الحالية.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <x-payment-dashboard.pagination :paginator="$reports" />
    </section>
</x-layouts.dashboard>
