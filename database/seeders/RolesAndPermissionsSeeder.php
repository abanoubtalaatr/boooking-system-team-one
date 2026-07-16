<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\AdminPermissionCatalog;
use Illuminate\Database\Seeder;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(AdminPermissionCatalog::all()) as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $superAdminRole = Role::findOrCreate('super-admin', 'web');
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('doctor', 'web');
        $superAdminRole->syncPermissions(Permission::query()->where('guard_name', 'web')->get());

        $rootById = User::query()->find(1);
        $rootByEmail = User::query()->where('email', 'camila.herman@example.net')->first();

        if ($rootById || $rootByEmail) {
            if (! $rootById || ! $rootByEmail || ! $rootById->is($rootByEmail)) {
                throw new RuntimeException('Super Admin ID and email do not identify the same user.');
            }

            $rootById->syncRoles([$superAdminRole]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
