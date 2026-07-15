@php
    use App\Enums\NoShowReportStatus;

    $reportStatuses = [
        NoShowReportStatus::PendingReview->value => ['قيد مراجعة الإدارة', 'warning'],
        NoShowReportStatus::Approved->value => ['تم القبول والتسوية', 'success'],
        NoShowReportStatus::Rejected->value => ['تم الرفض', 'danger'],
    ];
@endphp

<x-layouts.dashboard title="بلاغات عدم حضور المرضى" role="doctor">
    <div class="breadcrumb">الرئيسية / بلاغات عدم الحضور</div>
    <div class="page-head">
        <div>
            <h1 class="page-title">بلاغات عدم حضور المرضى</h1>
            <p class="page-description">قدّم بلاغًا للحجوزات المنتهية، وستراجع الإدارة الحالة قبل إلغاء العملية ورد العمولة.</p>
        </div>
        <span class="dashboard-live-indicator"><i></i> المراجعة تتم بواسطة الإدارة</span>
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
            ['حجوزات قابلة للبلاغ', $summary['eligible'], 'انتهى موعدها منذ أكثر من ساعة', 'calendar'],
            ['قيد المراجعة', $summary['pending'], 'لم تتخذ الإدارة قرارًا بعد', 'clock'],
            ['بلاغات مقبولة', $summary['approved'], 'تمت تسويتها ماليًا', 'shield'],
            ['بلاغات مرفوضة', $summary['rejected'], 'لم يتغير الحجز أو الرصيد', 'report'],
        ] as [$label, $value, $hint, $icon])
            <article class="stat"><div class="stat-top"><div><span class="stat-label">{{ $label }}</span><div class="stat-value">{{ number_format($value) }}</div><span class="stat-change">{{ $hint }}</span></div><span class="stat-icon"><x-ui-icon :name="$icon" /></span></div></article>
        @endforeach
    </section>

    <section class="panel payment-panel no-show-panel">
        <div class="panel-head payment-panel-head">
            <div><h2 class="panel-title">حجوزات يمكن الإبلاغ عنها</h2><p>لن يتغير الرصيد بمجرد إرسال البلاغ؛ التسوية تتم بعد موافقة الإدارة.</p></div>
            <span class="results-count">{{ number_format($eligibleBookings->total()) }} حجز</span>
        </div>
        <div class="payment-table-wrap">
            <table class="payment-table no-show-doctor-table">
                <thead><tr><th>الحجز</th><th>المريض</th><th>الموعد</th><th>الدفع والعمولة</th><th>إرسال البلاغ</th></tr></thead>
                <tbody>
                @forelse ($eligibleBookings as $booking)
                    <tr>
                        <td><strong>{{ $booking->booking_number }}</strong><small>{{ $booking->status->value === 'completed' ? 'مكتمل تلقائيًا' : 'مؤكد' }}</small></td>
                        <td><strong>{{ $booking->patient->name }}</strong><small>{{ $booking->patient->phone }}</small></td>
                        <td><strong>{{ $booking->slot->day->format('Y-m-d') }}</strong><small>{{ $booking->slot->start_time }} — {{ $booking->slot->end_time }}</small></td>
                        <td>
                            @if ($booking->latestPayment)
                                <strong>{{ $booking->latestPayment->method->value === 'cash' ? 'كاش' : 'فيزا' }}</strong>
                                <small>العمولة: {{ number_format($booking->latestPayment->commission_amount_cents / 100, 2) }} {{ $booking->latestPayment->currency }}</small>
                            @else
                                <strong>بدون عملية دفع</strong>
                            @endif
                        </td>
                        <td>
                            <form class="no-show-submit-form" method="POST" action="{{ route('web.doctor.no-show-reports.store', $booking) }}">
                                @csrf
                                <textarea name="reason" minlength="10" maxlength="2000" rows="2" required placeholder="اشرح ما حدث ومحاولات التواصل مع المريض"></textarea>
                                <button class="no-show-button no-show-button--report" type="submit">المريض لم يحضر</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td class="empty-payments" colspan="5">لا توجد حجوزات مؤهلة لبلاغ عدم الحضور حاليًا.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <x-payment-dashboard.pagination :paginator="$eligibleBookings" />
    </section>

    <section class="panel payment-panel no-show-panel">
        <div class="panel-head payment-panel-head">
            <div><h2 class="panel-title">بلاغاتي السابقة</h2><p>تابع قرار الإدارة وملاحظات المراجعة.</p></div>
            <span class="results-count">{{ number_format($reports->total()) }} بلاغ</span>
        </div>
        <div class="payment-table-wrap">
            <table class="payment-table no-show-history-table">
                <thead><tr><th>الحجز والمريض</th><th>سبب البلاغ</th><th>الحالة</th><th>ملاحظة الإدارة</th><th>تاريخ البلاغ</th></tr></thead>
                <tbody>
                @forelse ($reports as $report)
                    @php([$statusLabel, $statusTone] = $reportStatuses[$report->status->value])
                    <tr>
                        <td><strong>{{ $report->booking->booking_number }}</strong><small>{{ $report->booking->patient->name }}</small></td>
                        <td class="no-show-reason">{{ $report->reason }}</td>
                        <td><span class="payment-status payment-status--{{ $statusTone }}">{{ $statusLabel }}</span></td>
                        <td>{{ $report->review_note ?? 'لم تتم المراجعة بعد' }}@if($report->reviewed_at)<small>{{ $report->reviewed_at->format('Y-m-d H:i') }}</small>@endif</td>
                        <td><strong>{{ $report->created_at->format('Y-m-d') }}</strong><small>{{ $report->created_at->format('H:i') }}</small></td>
                    </tr>
                @empty
                    <tr><td class="empty-payments" colspan="5">لم تقدم أي بلاغات حتى الآن.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <x-payment-dashboard.pagination :paginator="$reports" />
    </section>
</x-layouts.dashboard>
