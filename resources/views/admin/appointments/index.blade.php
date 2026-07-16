<x-layouts.dashboard title="المواعيد" role="admin">
    <div class="breadcrumb">الرئيسية / المواعيد</div><div class="page-head"><div><h1 class="page-title">المواعيد المتاحة</h1><p class="page-description">متابعة جداول الأطباء وحالة الحجز.</p></div></div>
    <form class="mb-5 flex flex-wrap gap-3 rounded-xl bg-white p-4 shadow-sm" method="GET"><input class="rounded-lg border border-slate-200 px-4 py-3" type="date" name="date" value="{{ request('date') }}"><button class="primary-button">تطبيق</button></form>
    <div class="overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-right text-sm"><thead class="bg-slate-50"><tr><th class="p-4">الطبيب</th><th class="p-4">اليوم</th><th class="p-4">الوقت</th><th class="p-4">الحالة</th></tr></thead><tbody class="divide-y divide-slate-100">
        @forelse($slots as $slot)<tr><td class="p-4 font-semibold">{{ $slot->doctor?->name }}</td><td class="p-4">{{ $slot->day?->format('Y-m-d') }}</td><td class="p-4">{{ $slot->start_time }} - {{ $slot->end_time }}</td><td class="p-4">{{ $slot->is_booked ? 'محجوز' : 'متاح' }}</td></tr>@empty<tr><td class="p-8 text-center text-slate-500" colspan="4">لا توجد مواعيد.</td></tr>@endforelse
    </tbody></table></div><div class="mt-5">{{ $slots->links() }}</div>
</x-layouts.dashboard>
