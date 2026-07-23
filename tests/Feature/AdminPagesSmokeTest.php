<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders every newly added admin page for an authorized administrator', function (): void {
    $admin = User::factory()->admin()->create();

    foreach (['admin.patients', 'admin.specialties', 'admin.clinics', 'admin.appointments', 'admin.reports'] as $routeName) {
        $this->actingAs($admin)->get(route($routeName))->assertOk();
    }
});
