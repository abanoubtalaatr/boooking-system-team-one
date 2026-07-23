@php
    $bookingStatusLabels = [
        'pending' => 'قيد الانتظار', 'pending_payment' => 'بانتظار الدفع', 'confirmed' => 'مؤكد',
        'rejected' => 'مرفوض', 'completed' => 'مكتمل', 'cancelled' => 'ملغي',
        'rescheduled' => 'أُعيدت جدولته', 'expired' => 'منتهي', 'payment_failed' => 'فشل الدفع',
        'refund_pending' => 'استرداد معلق', 'refunded' => 'مسترد',
    ];
    $bookingStatusClasses = [
        'completed' => 'bg-emerald-100 text-emerald-700', 'confirmed' => 'bg-blue-100 text-blue-700',
        'pending' => 'bg-amber-100 text-amber-700', 'pending_payment' => 'bg-amber-100 text-amber-700',
        'cancelled' => 'bg-red-100 text-red-700', 'rejected' => 'bg-red-100 text-red-700',
        'payment_failed' => 'bg-red-100 text-red-700', 'refunded' => 'bg-slate-100 text-slate-700',
    ];
    $consultationLabels = ['clinic' => 'في العيادة', 'online' => 'أونلاين', 'home' => 'زيارة منزلية'];
    $currentPaginator = match ($activeTab) { 'visits' => $visits, 'reviews' => $reviews, default => $bookings };
@endphp

<div class="grid gap-6">
    <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <div class="h-24 bg-linear-to-l from-blue-700 to-cyan-500"></div>
        <div class="relative grid gap-5 p-6 pt-0 lg:grid-cols-[1fr_auto] lg:items-end">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="-mt-10 grid size-24 shrink-0 place-items-center overflow-hidden rounded-2xl border-4 border-white bg-blue-100 text-3xl font-black text-blue-700 shadow-sm">
                    @if ($patient->profile_photo)
                        <img class="size-full object-cover" src="{{ str($patient->profile_photo)->startsWith(['http://', 'https://']) ? $patient->profile_photo : asset('storage/'.$patient->profile_photo) }}" alt="صورة {{ $patient->name }}">
                    @else
                        {{ mb_substr($patient->name, 0, 1) }}
                    @endif
                </div>
                <div class="pb-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-black text-slate-900">{{ $patient->name }}</h1>
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $patient->isVerified() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $patient->isVerified() ? 'حساب موثق' : 'غير موثق' }}</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-500">رقم المريض #{{ $patient->id }} · مسجل منذ {{ $patient->created_at?->format('d/m/Y') }}</p>
                </div>
            </div>
            <a class="secondary-button inline-flex items-center justify-center gap-2" href="{{ route('admin.patients') }}"><x-ui-icon name="collapse" /> العودة للمرضى</a>
        </div>

        <div class="grid gap-px border-t border-slate-100 bg-slate-100 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white p-5"><span class="block text-xs text-slate-500">رقم الهاتف</span><strong class="mt-1 block" dir="ltr">{{ $patient->phone }}</strong></div>
            <div class="bg-white p-5"><span class="block text-xs text-slate-500">البريد الإلكتروني</span><strong class="mt-1 block break-all">{{ $patient->email }}</strong></div>
            <div class="bg-white p-5"><span class="block text-xs text-slate-500">تاريخ الميلاد</span><strong class="mt-1 block">{{ $patient->birthdate?->format('d/m/Y') ?? 'غير مسجل' }}</strong></div>
            <div class="bg-white p-5"><span class="block text-xs text-slate-500">الموقع</span><strong class="mt-1 block">{{ $patient->latitude && $patient->longitude ? $patient->latitude.', '.$patient->longitude : 'غير مسجل' }}</strong></div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm"><span class="text-sm text-slate-500">إجمالي الحجوزات</span><strong class="mt-2 block text-2xl text-slate-900">{{ $patient->patient_bookings_count }}</strong></div>
        <div class="rounded-xl bg-white p-5 shadow-sm"><span class="text-sm text-slate-500">الأطباء الذين زارهم</span><strong class="mt-2 block text-2xl text-slate-900">{{ $visitedDoctorsCount }}</strong></div>
        <div class="rounded-xl bg-white p-5 shadow-sm"><span class="text-sm text-slate-500">التقييمات والتعليقات</span><strong class="mt-2 block text-2xl text-slate-900">{{ $patient->reviews_count }}</strong></div>
    </section>

    <section class="rounded-2xl bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex gap-2 overflow-x-auto" role="tablist" aria-label="تفاصيل المريض">
                @foreach (['bookings' => 'كل الحجوزات', 'visits' => 'الأطباء والزيارات', 'reviews' => 'التقييمات والتعليقات'] as $tab => $label)
                    <button class="whitespace-nowrap rounded-lg px-4 py-2.5 text-sm font-bold transition {{ $activeTab === $tab ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}" type="button" wire:click="setTab('{{ $tab }}')" role="tab" aria-selected="{{ $activeTab === $tab ? 'true' : 'false' }}">{{ $label }}</button>
                @endforeach
            </div>
            <label class="relative w-full lg:max-w-sm" role="search">
                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400"><x-ui-icon name="search" /></span>
                <input class="w-full rounded-lg border border-slate-200 py-2.5 pr-11 pl-3 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100" type="search" wire:model.live.debounce.300ms="search" placeholder="بحث داخل التبويب الحالي" aria-label="بحث داخل بيانات المريض">
            </label>
        </div>

        <div class="overflow-x-auto" wire:loading.class="opacity-50" wire:target="setTab,search,gotoPage,previousPage,nextPage">
            @if ($activeTab === 'bookings')
                <table class="w-full min-w-240 text-right text-sm">
                    <thead class="bg-slate-50 text-slate-600"><tr><th class="p-4">رقم الحجز</th><th class="p-4">الطبيب</th><th class="p-4">الموعد</th><th class="p-4">نوع الكشف</th><th class="p-4">الدفع</th><th class="p-4">السعر</th><th class="p-4">الحالة</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($bookings as $booking)
                            @php $status = $booking->status->value; @endphp
                            <tr wire:key="booking-{{ $booking->id }}">
                                <td class="p-4 font-bold text-blue-700">{{ $booking->booking_number }}</td>
                                <td class="p-4"><strong class="block text-slate-900">د. {{ $booking->doctor->name }}</strong><small class="text-slate-500">{{ $booking->doctor->email }}</small></td>
                                <td class="p-4"><strong class="block">{{ $booking->booking_date->format('d/m/Y') }}</strong><small class="text-slate-500">{{ str($booking->booking_time->format('h:i A'))->replace(['AM', 'PM'], ['ص', 'م']) }}</small></td>
                                <td class="p-4">{{ $consultationLabels[$booking->consultation_type->value] ?? $booking->consultation_type->value }}</td>
                                <td class="p-4"><span class="rounded-full px-3 py-1 {{ match ($booking->latestPayment?->method?->value) { 'cash' => 'bg-emerald-100 text-emerald-700', 'card' => 'bg-violet-100 text-violet-700', default => 'bg-slate-100 text-slate-600' } }}">{{ match ($booking->latestPayment?->method?->value) { 'cash' => 'كاش', 'card' => 'فيزا', default => 'غير محدد' } }}</span></td>
                                <td class="p-4 font-semibold">{{ number_format((float) $booking->price, 2) }} EGP</td>
                                <td class="p-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $bookingStatusClasses[$status] ?? 'bg-slate-100 text-slate-700' }}">{{ $bookingStatusLabels[$status] ?? $status }}</span></td>
                            </tr>
                        @empty
                            <tr><td class="p-10 text-center text-slate-500" colspan="7">لا توجد حجوزات مطابقة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif ($activeTab === 'visits')
                <table class="w-full min-w-180 text-right text-sm">
                    <thead class="bg-slate-50 text-slate-600"><tr><th class="p-4">الطبيب</th><th class="p-4">التخصص</th><th class="p-4">تاريخ الزيارة</th><th class="p-4">نوع الزيارة</th><th class="p-4">رقم الحجز</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($visits as $visit)
                            <tr wire:key="visit-{{ $visit->id }}">
                                <td class="p-4"><strong class="block text-slate-900">د. {{ $visit->doctor->name }}</strong><small class="text-slate-500">{{ $visit->doctor->email }}</small></td>
                                <td class="p-4">{{ $visit->doctor->doctorProfile?->specialty?->name ?? 'غير محدد' }}</td>
                                <td class="p-4"><strong class="block">{{ $visit->booking_date->format('d/m/Y') }}</strong><small class="text-slate-500">{{ str($visit->booking_time->format('h:i A'))->replace(['AM', 'PM'], ['ص', 'م']) }}</small></td>
                                <td class="p-4">{{ $consultationLabels[$visit->consultation_type->value] ?? $visit->consultation_type->value }}</td>
                                <td class="p-4 font-bold text-blue-700">{{ $visit->booking_number }}</td>
                            </tr>
                        @empty
                            <tr><td class="p-10 text-center text-slate-500" colspan="5">لا توجد زيارات مكتملة لهذا المريض.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="w-full min-w-180 text-right text-sm">
                    <thead class="bg-slate-50 text-slate-600"><tr><th class="p-4">الطبيب</th><th class="p-4">التخصص</th><th class="p-4">التقييم</th><th class="p-4">التعليق</th><th class="p-4">التاريخ</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($reviews as $review)
                            <tr wire:key="review-{{ $review->id }}">
                                <td class="p-4"><strong class="block text-slate-900">د. {{ $review->doctor->name }}</strong><small class="text-slate-500">{{ $review->doctor->email }}</small></td>
                                <td class="p-4">{{ $review->doctor->doctorProfile?->specialty?->name ?? 'غير محدد' }}</td>
                                <td class="p-4"><span class="whitespace-nowrap text-amber-500" aria-label="{{ $review->rating }} من 5">@for ($star = 1; $star <= 5; $star++)<span class="{{ $star <= $review->rating ? '' : 'text-slate-200' }}">★</span>@endfor</span><small class="mr-2 text-slate-500">{{ $review->rating }}/5</small></td>
                                <td class="max-w-md p-4 text-slate-700">{{ $review->comment ?: 'لا يوجد تعليق مكتوب.' }}</td>
                                <td class="p-4">{{ $review->created_at?->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td class="p-10 text-center text-slate-500" colspan="5">لا توجد تقييمات أو تعليقات مطابقة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        @if ($currentPaginator?->hasPages())
            <div class="border-t border-slate-100 p-5">{{ $currentPaginator->links() }}</div>
        @endif
    </section>
</div>
