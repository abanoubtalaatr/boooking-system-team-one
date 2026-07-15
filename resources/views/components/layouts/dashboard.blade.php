@props(['title', 'role' => 'admin'])

@php
    $isDoctor = $role === 'doctor';
    $dashboardRoute = $isDoctor ? route('web.doctor.dashboard') : route('web.admin.dashboard');
    $navigation = $isDoctor
        ? [
            ['الرئيسية والمدفوعات', 'home', $dashboardRoute, request()->routeIs('web.doctor.dashboard')],
            ['المحفظة والسحب', 'report', route('web.doctor.wallet.index'), request()->routeIs('web.doctor.wallet.*')],
            ['حجوزاتي', 'calendar', '#', false],
            ['جدول المواعيد', 'clock', '#', false],
            ['المرضى', 'users', '#', false],
            ['الاستشارات', 'consultation', route('doctor.conversations'), request()->routeIs('doctor.conversations*')],
            ['التقييمات', 'star', '#', false],
            ['الملف المهني', 'doctor', '#', false],
        ]
        : [
            ['لوحة المدفوعات', 'home', $dashboardRoute, request()->routeIs('web.admin.dashboard')],
            ['الحجوزات', 'calendar', '#', false], ['الأطباء', 'doctor', '#', false], ['المرضى', 'users', '#', false],
            ['التخصصات', 'specialty', '#', false], ['العيادات', 'clinic', '#', false], ['المواعيد', 'clock', '#', false],
            ['التقارير', 'report', '#', false], ['المستخدمون والصلاحيات', 'shield', '#', false],
            ['طلبات السحب', 'report', route('web.admin.withdrawals.index'), request()->routeIs('web.admin.withdrawals.*')],
            ['إعدادات العمولات', 'settings', route('web.admin.payment-settings.edit'), request()->routeIs('web.admin.payment-settings.*')],
        ];
    $authenticatedUser = auth()->user();
    $personName = $isDoctor ? 'د. '.$authenticatedUser->name : $authenticatedUser->name;
    $personRole = $isDoctor ? 'طبيب استشاري' : 'مدير المنصة';
    $avatar = collect(preg_split('/\s+/u', $authenticatedUser->name, -1, PREG_SPLIT_NO_EMPTY))
        ->take(2)
        ->map(fn (string $part): string => mb_substr($part, 0, 1))
        ->implode('');
@endphp

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="لوحة تحكم منصة الأطباء">
    <title>{{ $title }} | منصة الأطباء</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="shell" data-shell>
    <aside class="sidebar" id="dashboard-sidebar" aria-label="القائمة الرئيسية">
        <div class="sidebar-brand">
            <x-brand-logo white />
            <span class="sidebar-copy"><span class="sidebar-title">منصة الأطباء</span><span class="sidebar-subtitle">{{ $isDoctor ? 'بوابة الطبيب' : 'نظام الإدارة' }}</span></span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">القائمة الرئيسية</div>
            @foreach ($navigation as [$label, $icon, $href, $active])
                <a class="nav-link {{ $active ? 'is-active' : '' }}" href="{{ $href }}" @if($active) aria-current="page" @endif>
                    <span class="nav-icon"><x-ui-icon :name="$icon" /></span><span class="sidebar-copy">{{ $label }}</span>
                </a>
            @endforeach
        </nav>
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="nav-link nav-button" type="submit"><span class="nav-icon"><x-ui-icon name="logout" /></span><span class="sidebar-copy">تسجيل الخروج</span></button>
            </form>
        </div>
    </aside>

    <button class="overlay" data-overlay type="button" aria-label="إغلاق القائمة"></button>

    <div class="shell-content">
        <header class="topbar">
            <button class="menu-button" data-menu-toggle type="button" aria-label="فتح القائمة" aria-controls="dashboard-sidebar" aria-expanded="false"><x-ui-icon name="menu" /></button>
            <button class="menu-button desktop-collapse" data-collapse type="button" aria-label="تصغير القائمة"><x-ui-icon name="collapse" /></button>
            <label class="search">
                <span><x-ui-icon name="search" /></span>
                <input type="search" placeholder="ابحث في المنصة..." aria-label="بحث عام">
            </label>
            <div class="topbar-actions">
                <button class="icon-button" type="button" aria-label="الإشعارات"><x-ui-icon name="bell" /><span class="notification-dot"></span></button>
                <div class="profile">
                    <button class="profile-button" data-profile-toggle type="button" aria-expanded="false" aria-controls="profile-menu">
                        <span class="avatar">{{ $avatar }}</span>
                        <span class="profile-copy"><span class="profile-name">{{ $personName }}</span><span class="profile-role">{{ $personRole }}</span></span>
                        <x-ui-icon name="chevron" />
                    </button>
                    <div class="profile-menu" id="profile-menu" data-profile-menu>
                        <a href="#">الملف الشخصي</a><a href="#">الإعدادات</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">تسجيل الخروج</button></form>
                    </div>
                </div>
            </div>
        </header>

        <main class="page">{{ $slot }}</main>
        <footer class="footer"><span>© {{ date('Y') }} منصة الأطباء. جميع الحقوق محفوظة.</span><span>الخصوصية · الشروط · الدعم</span></footer>
    </div>
</div>
</body>
</html>
