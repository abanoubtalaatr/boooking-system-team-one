<x-layouts.dashboard title="لوحة مدفوعات الإدارة" role="admin">
    <div class="breadcrumb">الرئيسية / المدفوعات</div>
    <div class="page-head">
        <div>
            <h1 class="page-title">إدارة كل المدفوعات</h1>
            <p class="page-description">متابعة عمليات الفيزا والكاش والعمولات ومستحقات الأطباء من مكان واحد.</p>
        </div>
        <span class="dashboard-live-indicator"><i></i> بيانات مباشرة من النظام</span>
    </div>

    <section class="stats payment-stats" aria-label="ملخص المدفوعات">
        @foreach ([
            ['إجمالي العمليات', number_format($summary['total_transactions']), 'كل الحالات المسجلة', 'report'],
            ['إجمالي المحصل', number_format($summary['gross_collected_cents'] / 100, 2).' EGP', 'فيزا وكاش تم تحصيله', 'calendar'],
            ['عمولة المنصة', number_format($summary['platform_fees_cents'] / 100, 2).' EGP', 'إجمالي مستحق المنصة', 'shield'],
            ['صافي الأطباء', number_format($summary['doctor_net_cents'] / 100, 2).' EGP', 'بعد خصم عمولة المنصة', 'doctor'],
        ] as [$label, $value, $hint, $icon])
            <article class="stat">
                <div class="stat-top">
                    <div><span class="stat-label">{{ $label }}</span><div class="stat-value stat-value--money">{{ $value }}</div><span class="stat-change">{{ $hint }}</span></div>
                    <span class="stat-icon"><x-ui-icon :name="$icon" /></span>
                </div>
            </article>
        @endforeach
    </section>

    <section class="payment-breakdown" aria-label="تفاصيل إضافية">
        <span><strong>{{ number_format($summary['card_collected_cents'] / 100, 2) }} EGP</strong> محصل بالفيزا</span>
        <span><strong>{{ number_format($summary['cash_collected_cents'] / 100, 2) }} EGP</strong> محصل كاش</span>
        <span><strong>{{ number_format($summary['pending_transactions']) }}</strong> عمليات معلقة</span>
        <span><strong>{{ number_format($summary['failed_transactions']) }}</strong> عمليات فاشلة</span>
        <span><strong>{{ number_format($summary['refunded_cents'] / 100, 2) }} EGP</strong> مبالغ مستردة</span>
    </section>

    <section class="panel payment-panel">
        <div class="panel-head payment-panel-head">
            <div><h2 class="panel-title">سجل عمليات الدفع</h2><p>يعرض جميع مدفوعات الأطباء والمرضى.</p></div>
            <span class="results-count">{{ number_format($payments->total()) }} نتيجة</span>
        </div>

        <x-payment-dashboard.filters :action="route('web.admin.dashboard')" :doctors="$doctors" show-doctor />
        <x-payment-dashboard.table :payments="$payments" show-doctor />
    </section>
</x-layouts.dashboard>
