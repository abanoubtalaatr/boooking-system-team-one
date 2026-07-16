<?php

use App\Models\User;
use App\Support\AdminPermissionCatalog;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('reserves the super admin role and every permission for the configured root account', function (): void {
    $root = User::factory()->doctor()->create([
        'email' => 'camila.herman@example.net',
    ]);

    expect($root->id)->toBe(1);

    $this->seed(RolesAndPermissionsSeeder::class);

    $root->refresh();

    expect($root->hasExactRoles('super-admin'))->toBeTrue()
        ->and($root->getAllPermissions()->pluck('name')->sort()->values()->all())
        ->toBe(collect(array_keys(AdminPermissionCatalog::all()))->sort()->values()->all());
});
