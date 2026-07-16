<x-layouts.dashboard title="إضافة أدمن" role="admin">
    <div class="breadcrumb">المستخدمون والصلاحيات / إضافة أدمن</div>
    <div class="page-head"><div><h1 class="page-title">إضافة حساب أدمن</h1><p class="page-description">ينشأ الحساب بدور Admin وحالة نشطة.</p></div></div>
    <form class="grid gap-6" method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <section class="grid gap-4 rounded-xl bg-white p-6 shadow-sm md:grid-cols-2">
            <label class="grid gap-2">الاسم<input class="rounded-lg border border-slate-200 px-4 py-3" name="name" value="{{ old('name') }}" required>@error('name')<small class="field-error">{{ $message }}</small>@enderror</label>
            <label class="grid gap-2">البريد<input class="rounded-lg border border-slate-200 px-4 py-3" type="email" name="email" value="{{ old('email') }}" required>@error('email')<small class="field-error">{{ $message }}</small>@enderror</label>
            <label class="grid gap-2">كلمة المرور<input class="rounded-lg border border-slate-200 px-4 py-3" type="password" name="password" required>@error('password')<small class="field-error">{{ $message }}</small>@enderror</label>
            <label class="grid gap-2">تأكيد كلمة المرور<input class="rounded-lg border border-slate-200 px-4 py-3" type="password" name="password_confirmation" required></label>
        </section>
        @can('admins.manage-permissions')<x-admin.permission-groups :groups="$permissionGroups" />@endcan
        <div class="settings-actions"><button class="primary-button" type="submit">إنشاء الحساب</button><a class="secondary-button" href="{{ route('admin.users.index') }}">إلغاء</a></div>
    </form>
</x-layouts.dashboard>
