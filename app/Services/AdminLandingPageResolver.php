<?php

namespace App\Services;

use App\Models\User;

class AdminLandingPageResolver
{
    /** @var array<string, string> */
    private const ROUTES_BY_PERMISSION = [
        'dashboard.view' => 'admin.dashboard',
        'bookings.view' => 'admin.bookings',
        'doctors.view' => 'admin.doctors',
        'patients.view' => 'admin.patients',
        'specialties.view' => 'admin.specialties',
        'clinics.view' => 'admin.clinics',
        'appointments.view' => 'admin.appointments',
        'reports.view' => 'admin.reports',
        'admins.view' => 'admin.users.index',
        'no-show-reports.view' => 'web.admin.no-show-reports.index',
        'withdrawals.view' => 'web.admin.withdrawals.index',
        'settings.view' => 'admin.settings',
    ];

    public function routeName(User $user): string
    {
        foreach (self::ROUTES_BY_PERMISSION as $permission => $routeName) {
            if ($user->can($permission)) {
                return $routeName;
            }
        }

        return 'admin.no-access';
    }
}
