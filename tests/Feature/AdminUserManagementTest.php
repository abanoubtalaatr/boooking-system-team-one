<?php

use App\Enums\UserStatus;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->rootAdmin = User::factory()->superAdmin()->create([
        'email' => 'camila.herman@example.net',
    ]);
    $this->manager = User::factory()->restrictedAdmin()->create();
    $this->manager->givePermissionTo([
        'admins.view',
        'admins.create',
        'admins.update',
        'admins.status',
        'admins.delete',
        'admins.manage-permissions',
    ]);
});

it('creates a regular administrator and records the action', function (): void {
    $this->actingAs($this->manager)->post(route('admin.users.store'), [
        'name' => 'Restricted Admin',
        'email' => 'restricted.admin@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'permissions' => ['dashboard.view'],
    ])->assertRedirect();

    $admin = User::query()->where('email', 'restricted.admin@example.test')->firstOrFail();

    expect($admin->hasExactRoles('admin'))->toBeTrue()
        ->and($admin->hasDirectPermission('dashboard.view'))->toBeTrue()
        ->and($admin->created_by)->toBe($this->manager->id)
        ->and(AdminAuditLog::query()->where('action', 'admin.created')->whereBelongsTo($admin, 'target')->exists())->toBeTrue();
});

it('synchronizes permissions and writes before and after values to the audit log', function (): void {
    $admin = User::factory()->restrictedAdmin()->create();

    $this->actingAs($this->manager)->put(route('admin.users.permissions', $admin), [
        'permissions' => ['dashboard.view', 'reports.view'],
    ])->assertRedirect();

    expect($admin->fresh()->getDirectPermissions()->pluck('name')->sort()->values()->all())
        ->toBe(['dashboard.view', 'reports.view']);

    $audit = AdminAuditLog::query()->where('action', 'admin.permissions-updated')->latest('id')->firstOrFail();
    expect($audit->before['permissions'])->toBe([])
        ->and($audit->after['permissions'])->toBe(['dashboard.view', 'reports.view']);
});

it('suspends an administrator and blocks old web and api access', function (): void {
    $admin = User::factory()->admin()->create();
    $token = $admin->createToken('test-session')->plainTextToken;

    $this->actingAs($this->manager)->patch(route('admin.users.status', $admin), [
        'status' => UserStatus::Suspended->value,
    ])->assertRedirect();

    $admin->refresh();

    expect($admin->status)->toBe(UserStatus::Suspended)
        ->and($admin->tokens()->count())->toBe(0);

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertForbidden();
    $this->withToken($token)->getJson('/api/admin/doctors')->assertUnauthorized();
});

it('prevents self-management and protects the reserved super admin', function (): void {
    $this->actingAs($this->manager)->put(route('admin.users.permissions', $this->manager), [
        'permissions' => ['dashboard.view'],
    ])->assertForbidden();

    $this->actingAs($this->manager)->patch(route('admin.users.status', $this->rootAdmin), [
        'status' => UserStatus::Suspended->value,
    ])->assertForbidden();

    $this->actingAs($this->manager)->delete(route('admin.users.destroy', $this->rootAdmin))->assertForbidden();
});

it('soft deletes an administrator from the trash action and records the action', function (): void {
    $admin = User::factory()->restrictedAdmin()->create();

    $this->actingAs($this->manager)
        ->delete(route('admin.users.destroy', $admin))
        ->assertRedirect(route('admin.users.index'));

    expect(User::query()->find($admin->id))->toBeNull()
        ->and(User::withTrashed()->find($admin->id)?->trashed())->toBeTrue()
        ->and(AdminAuditLog::query()->where('target_id', $admin->id)->where('action', 'admin.deleted')->exists())->toBeTrue();
});

it('shows pencil and trash icons instead of the open account text', function (): void {
    User::factory()->restrictedAdmin()->create(['name' => 'Icon Test Admin']);

    $this->actingAs($this->manager)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('تعديل حساب Icon Test Admin')
        ->assertSee('M4 20h4l11-11', false)
        ->assertSee('حذف حساب Icon Test Admin')
        ->assertDontSee('>فتح الحساب<', false);
});
