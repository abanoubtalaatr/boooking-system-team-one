<?php

namespace App\Support;

class AdminPermissionCatalog
{
    /** @return array<string, array{label: string, permissions: array<string, string>}> */
    public static function groups(): array
    {
        return [
            'dashboard' => ['label' => 'لوحة التحكم والمدفوعات', 'permissions' => ['dashboard.view' => 'عرض لوحة التحكم والمدفوعات']],
            'bookings' => ['label' => 'الحجوزات', 'permissions' => ['bookings.view' => 'عرض الحجوزات']],
            'doctors' => ['label' => 'الأطباء', 'permissions' => [
                'doctors.view' => 'عرض الأطباء', 'doctors.create' => 'إضافة طبيب', 'doctors.update' => 'تعديل طبيب',
                'doctors.approve' => 'اعتماد طبيب', 'doctors.suspend' => 'إيقاف طبيب', 'doctors.conversations.view' => 'عرض محادثات الأطباء',
            ]],
            'patients' => ['label' => 'المرضى', 'permissions' => [
                'patients.view' => 'عرض المرضى', 'patients.create' => 'إضافة مريض', 'patients.update' => 'تعديل مريض', 'patients.delete' => 'حذف مريض',
                'patients.favorites.view' => 'عرض المفضلة', 'patients.search-history.view' => 'عرض سجل البحث',
            ]],
            'specialties' => ['label' => 'التخصصات', 'permissions' => [
                'specialties.view' => 'عرض التخصصات', 'specialties.create' => 'إضافة تخصص', 'specialties.update' => 'تعديل تخصص', 'specialties.delete' => 'حذف تخصص',
            ]],
            'clinics' => ['label' => 'العيادات', 'permissions' => [
                'clinics.view' => 'عرض العيادات', 'clinics.create' => 'إضافة عيادة', 'clinics.update' => 'تعديل عيادة', 'clinics.delete' => 'حذف عيادة',
            ]],
            'appointments' => ['label' => 'المواعيد', 'permissions' => ['appointments.view' => 'عرض المواعيد']],
            'reports' => ['label' => 'التقارير', 'permissions' => ['reports.view' => 'عرض التقارير']],
            'no-show-reports' => ['label' => 'بلاغات عدم الحضور', 'permissions' => [
                'no-show-reports.view' => 'عرض البلاغات', 'no-show-reports.approve' => 'قبول البلاغ', 'no-show-reports.reject' => 'رفض البلاغ',
            ]],
            'withdrawals' => ['label' => 'طلبات السحب', 'permissions' => [
                'withdrawals.view' => 'عرض الطلبات', 'withdrawals.complete' => 'إتمام الطلب', 'withdrawals.cancel' => 'إلغاء الطلب',
            ]],
            'settings' => ['label' => 'إعدادات المنصة', 'permissions' => ['settings.view' => 'عرض الإعدادات', 'settings.update' => 'تعديل الإعدادات']],
            'admins' => ['label' => 'المستخدمون والصلاحيات', 'permissions' => [
                'admins.view' => 'عرض حسابات الأدمن', 'admins.create' => 'إضافة أدمن', 'admins.update' => 'تعديل أدمن',
                'admins.status' => 'تعطيل وتفعيل أدمن', 'admins.delete' => 'حذف أدمن', 'admins.manage-permissions' => 'إدارة الصلاحيات',
            ]],
        ];
    }

    /** @return array<string, string> */
    public static function all(): array
    {
        return collect(self::groups())->pluck('permissions')->collapse()->all();
    }
}
