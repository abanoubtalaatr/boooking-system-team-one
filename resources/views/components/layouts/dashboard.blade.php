@props(['title', 'role' => 'admin'])

@php
    $isDoctor = $role === 'doctor';
    $navigation = $isDoctor
    ? [
        ['الرئيسية', 'home', 'doctor.dashboard'],
        ['حجوزاتي', 'bookings', 'doctor.bookings'],
        ['جدول المواعيد', 'clock', 'doctor.schedule'],
        ['المرضى', 'users', 'doctor.patients'],
        ['الاستشارات', 'Conversations', 'doctor.conversations'],
        ['التقييمات', 'star', 'doctor.reviews'],
        ['الملف المهني', 'doctor', 'doctor.profile'],
    ]
    : [
        ['لوحة التحكم', 'home', 'admin.dashboard'],
        ['الحجوزات', 'bookings', 'admin.bookings'],
        ['الأطباء', 'doctor', 'admin.doctors'],
        ['المرضى', 'users', 'admin.patients'],
        ['التخصصات', 'specialty', 'admin.specialties'],
        ['العيادات', 'clinic', 'admin.clinics'],
        ['المواعيد', 'clock', 'admin.appointments'],
        ['التقارير', 'report', 'admin.reports'],
        ['المستخدمون والصلاحيات', 'shield', 'admin.users'],
        ['إعدادات المنصة', 'settings', 'admin.settings'],
    ];
    $personName = $isDoctor ? 'د. أحمد منصور' : 'محمد إسماعيل';
    $personRole = $isDoctor ? 'طبيب استشاري' : 'مدير المنصة';
@endphp

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="لوحة تحكم منصة الأطباء">
    <title>{{ $title }} | منصة الأطباء</title>
    <style>{!! file_get_contents(resource_path('css/app.css')) !!}</style>
        {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}

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
            @foreach ($navigation as [$label, $icon, $routeName])
                <a class="nav-link {{ request()->routeIs($routeName) ? 'is-active' : '' }}"
                href="{{ route($routeName) }}"
                @if(request()->routeIs($routeName)) aria-current="page" @endif>
                    <span class="nav-icon"><x-ui-icon :name="$icon" /></span>
                    <span class="sidebar-copy">{{ $label }}</span>
                </a>
            @endforeach
        </nav>
        <div class="sidebar-footer">
            <a class="nav-link" href="{{ route('login') }}"><span class="nav-icon"><x-ui-icon name="logout" /></span><span class="sidebar-copy">تسجيل الخروج</span></a>
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
                        <span class="avatar">{{ $isDoctor ? 'أم' : 'مإ' }}</span>
                        <span class="profile-copy"><span class="profile-name">{{ $personName }}</span><span class="profile-role">{{ $personRole }}</span></span>
                        <x-ui-icon name="chevron" />
                    </button>
                    <div class="profile-menu" id="profile-menu" data-profile-menu>
                        <a href="#">الملف الشخصي</a><a href="#">الإعدادات</a><a href="{{ route('login') }}">تسجيل الخروج</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="page">{{ $slot }}</main>
        <footer class="footer"><span>© {{ date('Y') }} منصة الأطباء. جميع الحقوق محفوظة.</span><span>الخصوصية · الشروط · الدعم</span></footer>
    </div>
</div>
<script>{!! file_get_contents(resource_path('js/app.js')) !!}</script>
</body>
</html>
