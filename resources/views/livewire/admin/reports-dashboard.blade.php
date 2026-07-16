@php
    $cardBookings = collect($report['payments'])->sum('card');
    $cashBookings = collect($report['payments'])->sum('cash');
    $paidBookings = max(1, $cardBookings + $cashBookings);
    $cardPercentage = round(($cardBookings / $paidBookings) * 100);
    $cashPercentage = round(($cashBookings / $paidBookings) * 100);
    $peakBooking = collect($report['bookings'])->sortByDesc('value')->first();
@endphp

<div class="relative grid gap-6">
    <div class="pointer-events-none fixed inset-0 z-50 hidden place-items-center bg-slate-950/10 backdrop-blur-[1px]" wire:loading.delay.class.remove="hidden" wire:loading.delay.class="grid" wire:target="setPeriod" aria-live="polite">
        <div class="flex items-center gap-3 rounded-2xl bg-white px-5 py-4 text-sm font-bold text-slate-700 shadow-2xl ring-1 ring-slate-200">
            <span class="size-5 animate-spin rounded-full border-2 border-blue-100 border-t-blue-700 motion-reduce:animate-none"></span>
            جاري تحديث التقرير
        </div>
    </div>

    <header class="relative isolate overflow-hidden rounded-3xl bg-linear-to-br from-slate-950 via-blue-950 to-blue-800 p-6 text-white shadow-xl shadow-blue-950/15 sm:p-8">
        <div class="absolute -top-24 -left-16 -z-10 size-64 rounded-full bg-cyan-400/15 blur-3xl"></div>
        <div class="absolute -right-20 -bottom-28 -z-10 size-72 rounded-full bg-blue-400/20 blur-3xl"></div>
        <div class="relative flex flex-col gap-7 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-2xl">
                <div class="mb-4 flex items-center gap-2 text-xs font-bold text-cyan-200"><span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-cyan-300 opacity-60 motion-reduce:animate-none"></span><span class="relative inline-flex size-2 rounded-full bg-cyan-300"></span></span>تقرير تشغيلي محدث</div>
                <h1 class="text-2xl font-black tracking-tight sm:text-3xl">نظرة شاملة على أداء المنصة</h1>
                <p class="mt-3 max-w-xl text-sm leading-7 text-blue-100/80">تابع حركة الحجوزات، توزيع طرق الدفع، وعمولة المنصة المحصلة من مكان واحد.</p>
                <div class="mt-5 flex flex-wrap gap-2 text-xs font-bold text-blue-100">
                    <span class="rounded-full border border-white/10 bg-white/10 px-3 py-2 backdrop-blur-sm"><x-ui-icon name="calendar" class="ml-1 inline size-4" /> {{ $report['period']['range'] }}</span>
                    <span class="rounded-full border border-white/10 bg-white/10 px-3 py-2 backdrop-blur-sm">X الزمن · Y القيمة</span>
                </div>
            </div>

            <div class="w-full xl:w-auto">
                <span class="mb-2 block text-xs font-bold text-blue-100/70">الفترة الزمنية</span>
                <div class="grid grid-cols-3 gap-1 rounded-2xl border border-white/10 bg-slate-950/25 p-1.5 backdrop-blur-md" aria-label="اختيار الفترة الزمنية">
                    @foreach (['week' => '7 أيام', 'forty_days' => '40 يومًا', 'year' => '12 شهرًا'] as $periodKey => $periodLabel)
                        <button class="rounded-xl px-4 py-3 text-sm font-bold whitespace-nowrap transition duration-200 data-loading:pointer-events-none data-loading:opacity-60 {{ $period === $periodKey ? 'bg-white text-blue-950 shadow-lg' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}" type="button" wire:click="setPeriod('{{ $periodKey }}')" wire:loading.attr="disabled" wire:target="setPeriod" aria-pressed="{{ $period === $periodKey ? 'true' : 'false' }}">{{ $periodLabel }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    </header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="مؤشرات التقرير الرئيسية">
        <x-admin.report-stat-card label="إجمالي الحجوزات" :value="number_format($report['summary']['bookings'])" :caption="$report['period']['label']" icon="calendar" tone="blue" />
        <x-admin.report-stat-card label="متوسط الحجوزات" :value="number_format($report['summary']['average'], 1)" :caption="'لكل '.($report['period']['unit'] === 'month' ? 'شهر' : 'يوم')" icon="report" tone="cyan" />
        <x-admin.report-stat-card label="الحجوزات المدفوعة" :value="number_format($report['summary']['paid_bookings'])" caption="فيزا وكاش محصل" icon="shield" tone="violet" />
        <x-admin.report-stat-card label="ربح المنصة" :value="number_format($report['summary']['profit_cents'] / 100, 2).' EGP'" :caption="$report['summary']['trend'] === null ? 'لا توجد فترة سابقة للمقارنة' : (($report['summary']['trend'] >= 0 ? 'ارتفاع ' : 'انخفاض ').number_format(abs($report['summary']['trend']), 1).'% في الحجوزات')" icon="star" tone="emerald" direction="ltr" />
    </section>

    <section class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
            <div class="flex items-start gap-3"><span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700 ring-1 ring-blue-100"><x-ui-icon name="report" /></span><div><h2 class="text-lg font-black text-slate-950">معدل الحجوزات</h2><p class="mt-1 text-sm text-slate-500">حركة إنشاء الحجوزات عبر الفترة المختارة.</p></div></div>
            <div class="flex flex-wrap gap-2 text-xs font-bold"><span class="rounded-full bg-blue-50 px-3 py-2 text-blue-700">المتوسط {{ number_format($report['summary']['average'], 1) }}</span><span class="rounded-full bg-slate-100 px-3 py-2 text-slate-600">الذروة {{ number_format($peakBooking['value'] ?? 0) }} حجز</span></div>
        </div>
        <div class="p-3 sm:p-6"><x-admin.report-line-chart :series="$report['bookings']" chart-id="bookings-rate" label="معدل الحجوزات خلال الفترة" /></div>
    </section>

    <div class="grid gap-6 2xl:grid-cols-2">
        <section class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-5 sm:px-7">
                <div class="flex items-start gap-3"><span class="grid size-11 shrink-0 place-items-center rounded-xl bg-violet-50 text-violet-700 ring-1 ring-violet-100"><x-ui-icon name="calendar" /></span><div><h2 class="text-lg font-black text-slate-950">الفيزا مقابل الكاش</h2><p class="mt-1 text-sm text-slate-500">الحجوزات المكتملة التحصيل فقط.</p></div></div>
                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-violet-50/70 p-4 ring-1 ring-violet-100"><div class="flex items-center justify-between gap-2 text-xs font-bold text-violet-700"><span class="flex items-center gap-2"><i class="size-2.5 rounded-full bg-violet-600"></i>فيزا</span><span>{{ $cardPercentage }}%</span></div><strong class="mt-2 block text-2xl font-black text-violet-950">{{ number_format($cardBookings) }}</strong><small class="mt-2 block text-violet-600/70">من الحجوزات المدفوعة</small></div>
                    <div class="rounded-2xl bg-emerald-50/70 p-4 ring-1 ring-emerald-100"><div class="flex items-center justify-between gap-2 text-xs font-bold text-emerald-700"><span class="flex items-center gap-2"><i class="size-2.5 rounded-full bg-emerald-500"></i>كاش</span><span>{{ $cashPercentage }}%</span></div><strong class="mt-2 block text-2xl font-black text-emerald-950">{{ number_format($cashBookings) }}</strong><small class="mt-2 block text-emerald-600/70">من الحجوزات المدفوعة</small></div>
                </div>
            </div>
            <div class="p-3 sm:p-6"><x-admin.report-payment-chart :series="$report['payments']" /></div>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-start sm:justify-between sm:px-7">
                <div class="flex items-start gap-3"><span class="grid size-11 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100"><x-ui-icon name="star" /></span><div><h2 class="text-lg font-black text-slate-950">أرباح المنصة</h2><p class="mt-1 text-sm text-slate-500">العمولة المحصلة دون المعلق والمسترد.</p></div></div>
                <div class="rounded-xl bg-emerald-50 px-4 py-3 text-left ring-1 ring-emerald-100" dir="ltr"><small class="block text-xs font-bold text-emerald-700">TOTAL PROFIT</small><strong class="mt-1 block text-xl font-black text-emerald-950">{{ number_format($report['summary']['profit_cents'] / 100, 2) }} EGP</strong></div>
            </div>
            <div class="p-3 sm:p-6"><x-admin.report-line-chart :series="$report['profits']" chart-id="platform-profit" color="#059669" money label="ربح المنصة خلال الفترة" /></div>
        </section>
    </div>

    <footer class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between"><span>يتم احتساب الأرباح من العمليات المحصلة فعليًا فقط.</span><span>الفترة الحالية: <strong class="text-slate-700">{{ $report['period']['label'] }}</strong></span></footer>
</div>
