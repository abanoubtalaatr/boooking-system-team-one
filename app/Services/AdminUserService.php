<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AdminUserService
{
    /** @param array{name: string, email: string, password: string, permissions?: array<int, string>} $data */
    public function create(array $data, User $actor, array $context): User
    {
        return DB::transaction(function () use ($data, $actor, $context): User {
            $admin = User::query()->create([
                ...Arr::only($data, ['name', 'email', 'password']),
                'created_by' => $actor->id,
                'status' => UserStatus::Active,
            ]);
            $admin->assignRole('admin');
            $admin->syncPermissions($data['permissions'] ?? []);
            $this->audit($actor, $admin, 'admin.created', null, $this->snapshot($admin), $context);

            return $admin;
        });
    }

    public function update(User $admin, array $data, User $actor, array $context): void
    {
        DB::transaction(function () use ($admin, $data, $actor, $context): void {
            $before = $this->snapshot($admin);
            $admin->update(array_filter(Arr::only($data, ['name', 'email', 'password']), fn ($value) => filled($value)));
            $this->audit($actor, $admin, 'admin.updated', $before, $this->snapshot($admin->fresh()), $context);
        });
    }

    public function updateStatus(User $admin, UserStatus $status, User $actor, array $context): void
    {
        DB::transaction(function () use ($admin, $status, $actor, $context): void {
            $before = $this->snapshot($admin);
            $admin->update(['status' => $status]);

            if ($status === UserStatus::Suspended) {
                $admin->tokens()->delete();
                DB::table('sessions')->where('user_id', $admin->id)->delete();
            }

            $this->audit($actor, $admin, 'admin.status-updated', $before, $this->snapshot($admin->fresh()), $context);
        });
    }

    public function delete(User $admin, User $actor, array $context): void
    {
        DB::transaction(function () use ($admin, $actor, $context): void {
            $before = $this->snapshot($admin);
            $admin->tokens()->delete();
            DB::table('sessions')->where('user_id', $admin->id)->delete();
            $admin->delete();
            $this->audit($actor, $admin, 'admin.deleted', $before, $this->snapshot($admin), $context);
        });
    }

    /** @param array<int, string> $permissions */
    public function syncPermissions(User $admin, array $permissions, User $actor, array $context): void
    {
        DB::transaction(function () use ($admin, $permissions, $actor, $context): void {
            $before = $this->snapshot($admin);
            $admin->syncPermissions($permissions);
            $this->audit($actor, $admin, 'admin.permissions-updated', $before, $this->snapshot($admin->fresh()), $context);
        });
    }

    private function snapshot(User $admin): array
    {
        return [
            'name' => $admin->name,
            'email' => $admin->email,
            'status' => $admin->status->value,
            'deleted_at' => $admin->deleted_at?->toISOString(),
            'permissions' => $admin->getDirectPermissions()->pluck('name')->sort()->values()->all(),
        ];
    }

    private function audit(User $actor, User $target, string $action, ?array $before, array $after, array $context): void
    {
        AdminAuditLog::query()->create([
            'actor_id' => $actor->id,
            'target_id' => $target->id,
            'action' => $action,
            'before' => $before,
            'after' => $after,
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);
    }
}
