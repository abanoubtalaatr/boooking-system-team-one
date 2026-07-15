<x-layouts.dashboard title="لوحة مدفوعات الطبيب" role="doctor">
    <div class="breadcrumb">الرئيسية / مدفوعاتي</div>
    <div class="page-head">
        <div>
            <h1 class="page-title">مرحباً، د. {{ $dashboard['doctor']->name }}</h1>
            <p class="page-description">ملخص حجوزاتك ومحفظتك وكل عمليات الدفع الخاصة بك فقط.</p>
        </div>
        <span class="commission-chip">فيزا {{ $dashboard['current_commission']['card']['percentage'] }}% · كاش {{ $dashboard['current_commission']['cash']['percentage'] }}%</span>
    </div>

    <section class="stats payment-stats" aria-label="ملخص مدفوعات الطبيب">
        @foreach ([
            ['رصيد المحفظة', number_format($dashboard['wallet']['balance_cents'] / 100, 2).' '.$dashboard['wallet']['currency'], $dashboard['wallet']['can_withdraw'] ? 'متاح للسحب' : 'غير متاح للسحب حالياً', 'report'],
            ['إجمالي المحصل', number_format($dashboard['payments']['gross_revenue_cents'] / 100, 2).' EGP', $dashboard['payments']['completed_transactions'].' عمليات مكتملة', 'calendar'],
            ['عمولة المنصة', number_format($dashboard['payments']['platform_fees_cents'] / 100, 2).' EGP', 'من عملياتك المكتملة', 'shield'],
            ['صافي مستحقاتك', number_format($dashboard['payments']['doctor_net_revenue_cents'] / 100, 2).' EGP', 'بعد خصم العمولة', 'doctor'],
        ] as [$label, $value, $hint, $icon])
            <article class="stat">
                <div class="stat-top">
                    <div><span class="stat-label">{{ $label }}</span><div class="stat-value stat-value--money">{{ $value }}</div><span class="stat-change">{{ $hint }}</span></div>
                    <span class="stat-icon"><x-ui-icon :name="$icon" /></span>
                </div>
            </article>
        @endforeach
    </section>

    <section class="payment-breakdown" aria-label="تفاصيل حجوزات الطبيب">
        <span><strong>{{ number_format($dashboard['payments']['card_net_revenue_cents'] / 100, 2) }} EGP</strong> صافي الفيزا</span>
        <span><strong>{{ number_format($dashboard['payments']['cash_gross_collected_cents'] / 100, 2) }} EGP</strong> كاش محصل</span>
        <span><strong>{{ number_format($dashboard['payments']['pending_card_cents'] / 100, 2) }} EGP</strong> فيزا معلقة</span>
        <span><strong>{{ number_format($dashboard['bookings']['confirmed']) }}</strong> حجوزات مؤكدة</span>
        <span><strong>{{ number_format($dashboard['payments']['failed_transactions']) }}</strong> عمليات فاشلة</span>
    </section>

    <section class="panel payment-panel">
        <div class="panel-head payment-panel-head">
            <div><h2 class="panel-title">مدفوعاتي</h2><p>هذه القائمة مقيدة تلقائياً بحسابك ولا تعرض بيانات أي طبيب آخر.</p></div>
            <span class="results-count">{{ number_format($payments->total()) }} نتيجة</span>
        </div>

        <x-payment-dashboard.filters :action="route('web.doctor.dashboard')" />
        <x-payment-dashboard.table :payments="$payments" />
    </section>
</x-layouts.dashboard>
