<x-layouts.dashboard title="تعديل الأدمن" role="admin">
    <div class="breadcrumb">المستخدمون والصلاحيات / {{ $admin->name }}</div>
    <div class="page-head"><div><h1 class="page-title">{{ $admin->name }}</h1><p class="page-description">{{ $admin->email }} · {{ $admin->hasRole('super-admin') ? 'Super Admin' : 'Admin' }}</p></div></div>
    @if (session('success'))<div class="settings-alert settings-alert--success" role="status">{{ session('success') }}</div>@endif

    <div class="grid gap-6 xl:grid-cols-[380px_1fr]">
        <div class="grid content-start gap-5">
            <form class="grid gap-4 rounded-xl bg-white p-6 shadow-sm" method="POST" action="{{ route('admin.users.update', $admin) }}">
                @csrf @method('PUT')
                <h2 class="text-lg font-bold">بيانات الحساب</h2>
                <label class="grid gap-2">الاسم<input class="rounded-lg border border-slate-200 px-4 py-3" name="name" value="{{ old('name', $admin->name) }}" @disabled($isProtectedSuperAdmin) required></label>
                <label class="grid gap-2">البريد<input class="rounded-lg border border-slate-200 px-4 py-3" type="email" name="email" value="{{ old('email', $admin->email) }}" @disabled($isProtectedSuperAdmin) required></label>
                <label class="grid gap-2">كلمة مرور جديدة<input class="rounded-lg border border-slate-200 px-4 py-3" type="password" name="password" @disabled($isProtectedSuperAdmin)></label>
                <label class="grid gap-2">تأكيد كلمة المرور<input class="rounded-lg border border-slate-200 px-4 py-3" type="password" name="password_confirmation" @disabled($isProtectedSuperAdmin)></label>
                @can('admins.update')<button class="primary-button" type="submit" @disabled($isProtectedSuperAdmin)>حفظ البيانات</button>@endcan
            </form>

            @can('admins.status')
                @unless($isProtectedSuperAdmin || auth()->user()->is($admin))
                    <form class="rounded-xl bg-white p-6 shadow-sm" method="POST" action="{{ route('admin.users.status', $admin) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $admin->isSuspended() ? 'active' : 'suspended' }}">
                        <button class="{{ $admin->isSuspended() ? 'primary-button' : 'secondary-button' }}" type="submit">{{ $admin->isSuspended() ? 'تفعيل الحساب' : 'تعطيل الحساب' }}</button>
                    </form>
                @endunless
            @endcan
        </div>

        <form class="grid gap-5" method="POST" action="{{ route('admin.users.permissions', $admin) }}">
            @csrf @method('PUT')
            <div><h2 class="text-xl font-bold">الصلاحيات</h2><p class="mt-1 text-sm text-slate-500">اختر مجموعة كاملة أو صلاحيات منفردة.</p></div>
            <x-admin.permission-groups :groups="$permissionGroups" :selected="$isProtectedSuperAdmin ? array_keys(\App\Support\AdminPermissionCatalog::all()) : $selectedPermissions" :disabled="$isProtectedSuperAdmin || auth()->user()->is($admin) || ! auth()->user()->can('admins.manage-permissions')" />
            @can('admins.manage-permissions')
                @unless($isProtectedSuperAdmin || auth()->user()->is($admin))<button class="primary-button" type="submit">حفظ الصلاحيات</button>@endunless
            @endcan
        </form>
    </div>
</x-layouts.dashboard>
