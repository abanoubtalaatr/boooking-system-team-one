@props(['title', 'role' => 'admin'])

@php
    $authenticatedUser = auth()->user();
    $isDoctor = $authenticatedUser->isDoctor();
    $dashboardRoute = $isDoctor ? route('web.doctor.dashboard') : route('admin.dashboard');
    $navigation = $isDoctor
        ? [
            ['الرئيسية والمدفوعات', 'home', $dashboardRoute, request()->routeIs('web.doctor.dashboard'), null],
            ['المحفظة والسحب', 'report', route('web.doctor.wallet.index'), request()->routeIs('web.doctor.wallet.*'), null],
            ['بلاغات عدم الحضور', 'calendar', route('web.doctor.no-show-reports.index'), request()->routeIs('web.doctor.no-show-reports.*'), null],
            ['حجوزاتي', 'bookings', route('doctor.bookings'), request()->routeIs('doctor.bookings'), null],
            ['جدول المواعيد', 'clock', route('doctor.availability-slots.index'), request()->routeIs('doctor.schedule') || request()->routeIs('doctor.availability-slots.*'), null],
            ['المرضى', 'users', route('doctor.patients'), request()->routeIs('doctor.patients'), null],
            ['الاستشارات', 'consultation', route('doctor.conversations'), request()->routeIs('doctor.conversations*'), null],
            ['التقييمات', 'star', route('doctor.reviews'), request()->routeIs('doctor.reviews'), null],
            ['الملف المهني', 'doctor', route('doctor.profile'), request()->routeIs('doctor.profile'), null],
        ]
        : [
            ['لوحة التحكم', 'home', $dashboardRoute, request()->routeIs('admin.dashboard'), 'dashboard.view'],
            ['الحجوزات', 'bookings', route('admin.bookings'), request()->routeIs('admin.bookings*'), 'bookings.view'],
            ['الأطباء', 'doctor', route('admin.doctors'), request()->routeIs('admin.doctors*'), 'doctors.view'],
            ['المرضى', 'users', route('admin.patients'), request()->routeIs('admin.patients*'), 'patients.view'],
            ['المفضلة لدى المرضى', 'star', route('admin.patient-favorites'), request()->routeIs('admin.patient-favorites*'), 'patients.favorites.view'],
            ['سجل البحث', 'search', route('admin.search-history'), request()->routeIs('admin.search-history*'), 'patients.search-history.view'],
            ['التخصصات', 'specialty', route('admin.specialties'), request()->routeIs('admin.specialties'), 'specialties.view'],
            ['العيادات', 'clinic', route('admin.hospitals.index'), request()->routeIs('admin.clinics') || request()->routeIs('admin.hospitals.*'), 'clinics.view'],
            ['المواعيد', 'clock', route('admin.availability-slots.index'), request()->routeIs('admin.appointments') || request()->routeIs('admin.availability-slots.*'), 'appointments.view'],
            ['التقارير', 'report', route('admin.reports'), request()->routeIs('admin.reports'), 'reports.view'],
            ['المستخدمون والصلاحيات', 'shield', route('admin.users.index'), request()->routeIs('admin.users.*'), 'admins.view'],
            ['بلاغات عدم الحضور', 'calendar', route('web.admin.no-show-reports.index'), request()->routeIs('web.admin.no-show-reports.*'), 'no-show-reports.view'],
            ['طلبات السحب', 'report', route('web.admin.withdrawals.index'), request()->routeIs('web.admin.withdrawals.*'), 'withdrawals.view'],
            ['إعدادات المنصة', 'settings', route('admin.settings'), request()->routeIs('admin.settings*'), 'settings.view'],
        ];
    $navigation = array_values(array_filter($navigation, fn (array $item): bool => $item[4] === null || $authenticatedUser->can($item[4])));
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
            @foreach ($navigation as [$label, $icon, $href, $active, $permission])
                <a class="nav-link {{ $active ? 'is-active' : '' }}" href="{{ $href }}" @if($active) aria-current="page" @endif>
                    <span class="nav-icon"><x-ui-icon :name="$icon" /></span><span class="sidebar-copy">{{ $label }}</span>
                </a>
            @endforeach
        </nav>
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
                    <div class="profile-menu" id="profile-menu" data-profile-menu role="menu" aria-label="قائمة الحساب" hidden>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" role="menuitem"><x-ui-icon name="logout" /> تسجيل الخروج</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        @if(session('success'))
            <div class="mx-6 mt-4 rounded-lg border border-green-300 bg-green-50 px-5 py-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mx-6 mt-4 rounded-lg border border-red-300 bg-red-50 px-5 py-4 text-red-800">
                {{ session('error') }}
            </div>
        @endif
        <main class="page">{{ $slot }}</main>
        <footer class="footer"><span>© {{ date('Y') }} منصة الأطباء. جميع الحقوق محفوظة.</span><span>الخصوصية · الشروط · الدعم</span></footer>
    </div>
</div>
</body>
</html>
