<x-layouts.dashboard title="التخصصات" role="admin">
    <div class="breadcrumb">الرئيسية / التخصصات</div><div class="page-head"><div><h1 class="page-title">التخصصات</h1><p class="page-description">قائمة تخصصات الأطباء المسجلة.</p></div></div>
    <div class="overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-right text-sm"><thead class="bg-slate-50"><tr><th class="p-4">التخصص</th><th class="p-4">عدد الأطباء</th><th class="p-4">تاريخ الإضافة</th></tr></thead><tbody class="divide-y divide-slate-100">
        @forelse($specializations as $specialization)<tr><td class="p-4 font-semibold">{{ $specialization->name }}</td><td class="p-4">{{ $specialization->doctors_count }}</td><td class="p-4">{{ $specialization->created_at?->format('Y-m-d') }}</td></tr>@empty<tr><td class="p-8 text-center text-slate-500" colspan="3">لا توجد تخصصات.</td></tr>@endforelse
    </tbody></table></div><div class="mt-5">{{ $specializations->links() }}</div>
</x-layouts.dashboard>
