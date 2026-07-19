<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        foreach (['super-admin', 'admin', 'doctor'] as $role) {
            DB::table('roles')->insertOrIgnore([
                'name' => $role,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $roles = DB::table('roles')->where('guard_name', 'web')->pluck('id', 'name');
        $users = DB::table('users')->get(['id', 'email', 'role']);

        foreach ($users as $user) {
            throw_unless(in_array($user->role, ['admin', 'doctor'], true), new RuntimeException("Unsupported legacy user role [{$user->role}]."));

            $isRootAdmin = (int) $user->id === 1 && $user->email === 'camila.herman@example.net';
            $roleName = $isRootAdmin ? 'super-admin' : $user->role;

            DB::table('model_has_roles')->insertOrIgnore([
                'role_id' => $roles[$roleName],
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('model_has_roles')->where('model_type', User::class)->delete();
        DB::table('role_has_permissions')->delete();
        DB::table('roles')->whereIn('name', ['super-admin', 'admin', 'doctor'])->where('guard_name', 'web')->delete();
    }
};
