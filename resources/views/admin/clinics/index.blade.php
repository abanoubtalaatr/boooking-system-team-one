<x-layouts.dashboard title="العيادات" role="admin">
    <div class="breadcrumb">الرئيسية / العيادات</div><div class="page-head"><div><h1 class="page-title">العيادات والمستشفيات</h1><p class="page-description">عرض الجهات الطبية وعدد ملفات الأطباء المرتبطة.</p></div></div>
    <div class="overflow-x-auto rounded-xl bg-white shadow-sm"><table class="w-full text-right text-sm"><thead class="bg-slate-50"><tr><th class="p-4">الاسم</th><th class="p-4">العنوان</th><th class="p-4">الأطباء</th></tr></thead><tbody class="divide-y divide-slate-100">
        @forelse($hospitals as $hospital)<tr><td class="p-4 font-semibold">{{ $hospital->name }}</td><td class="p-4">{{ $hospital->address ?: '—' }}</td><td class="p-4">{{ $hospital->doctor_profiles_count }}</td></tr>@empty<tr><td class="p-8 text-center text-slate-500" colspan="3">لا توجد عيادات.</td></tr>@endforelse
    </tbody></table></div><div class="mt-5">{{ $hospitals->links() }}</div>
</x-layouts.dashboard>
