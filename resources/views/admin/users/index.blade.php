<x-layouts.dashboard title="المستخدمون والصلاحيات" role="admin">
    <div class="breadcrumb">الرئيسية / المستخدمون والصلاحيات</div>
    <div class="page-head">
        <div><h1 class="page-title">حسابات الأدمن</h1><p class="page-description">إدارة الحسابات والصلاحيات وحالة الوصول إلى لوحة الإدارة.</p></div>
        @can('admins.create')<a class="primary-button" href="{{ route('admin.users.create') }}">إضافة أدمن</a>@endcan
    </div>

    @if (session('success'))<div class="settings-alert settings-alert--success" role="status">{{ session('success') }}</div>@endif

    <form class="mb-5 grid gap-3 rounded-xl bg-white p-4 shadow-sm md:grid-cols-[1fr_220px_auto]" method="GET">
        <input class="rounded-lg border border-slate-200 px-4 py-3" name="search" value="{{ request('search') }}" placeholder="بحث بالاسم أو البريد">
        <select class="rounded-lg border border-slate-200 px-4 py-3" name="status">
            <option value="">كل الحالات</option><option value="active" @selected(request('status') === 'active')>نشط</option><option value="suspended" @selected(request('status') === 'suspended')>موقوف</option>
        </select>
        <button class="primary-button" type="submit">بحث</button>
    </form>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-right text-sm">
            <thead class="bg-slate-50 text-slate-600"><tr><th class="p-4">الأدمن</th><th class="p-4">الدور</th><th class="p-4">الحالة</th><th class="p-4">الصلاحيات</th><th class="p-4">الإجراءات</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($admins as $admin)
                <tr>
                    <td class="p-4"><strong class="block text-slate-900">{{ $admin->name }}</strong><span class="text-slate-500">{{ $admin->email }}</span></td>
                    <td class="p-4">{{ $admin->hasRole('super-admin') ? 'Super Admin' : 'Admin' }}</td>
                    <td class="p-4"><span class="rounded-full px-3 py-1 {{ $admin->isSuspended() ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $admin->isSuspended() ? 'موقوف' : 'نشط' }}</span></td>
                    <td class="p-4">{{ $admin->getAllPermissions()->count() }}</td>
                    <td class="p-4">
                        <div class="flex items-center gap-2">
                            <a class="inline-flex size-10 items-center justify-center rounded-full bg-blue-50 text-blue-600 transition duration-700 ease-in-out hover:scale-110 hover:bg-blue-100 motion-reduce:transition-none motion-reduce:hover:transform-none" href="{{ route('admin.users.edit', $admin) }}" aria-label="تعديل حساب {{ $admin->name }}" title="تعديل الحساب">
                                <x-ui-icon name="edit" class="size-5" />
                            </a>
                            @can('admins.delete')
                                @unless((($admin->id === 1) && ($admin->email === 'camila.herman@example.net')) || auth()->user()->is($admin))
                                    <form method="POST" action="{{ route('admin.users.destroy', $admin) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا الحساب؟')">
                                        @csrf @method('DELETE')
                                        <button class="inline-flex size-10 items-center justify-center rounded-full bg-red-50 text-red-600 transition duration-700 ease-in-out hover:-rotate-12 hover:scale-110 hover:bg-red-600 hover:text-white motion-reduce:transition-none motion-reduce:hover:transform-none" type="submit" aria-label="حذف حساب {{ $admin->name }}" title="حذف الحساب">
                                            <x-ui-icon name="trash" class="size-5" />
                                        </button>
                                    </form>
                                @endunless
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td class="p-8 text-center text-slate-500" colspan="5">لا توجد حسابات مطابقة.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $admins->links() }}</div>
</x-layouts.dashboard>
