<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('protects web pages with operation permissions', function (): void {
    $admin = User::factory()->restrictedAdmin()->create();

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertForbidden();

    $admin->givePermissionTo('dashboard.view');

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee(route('admin.doctors'));
});

it('protects sanctum api routes with the same permissions', function (): void {
    $admin = User::factory()->restrictedAdmin()->create();
    Sanctum::actingAs($admin);

    $this->getJson('/api/admin/doctors')->assertForbidden();

    $admin->givePermissionTo('doctors.view');

    $this->getJson('/api/admin/doctors')->assertOk();
});

it('allows the super admin through every permission gate', function (): void {
    $superAdmin = User::factory()->superAdmin()->create();

    $this->actingAs($superAdmin)->get(route('admin.dashboard'))->assertOk();
    $this->actingAs($superAdmin)->get(route('admin.users.index'))->assertOk();
});
