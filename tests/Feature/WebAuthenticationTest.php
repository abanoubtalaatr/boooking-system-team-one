<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('web login page contains a working authentication form', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('action="'.route('login.store').'"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false);
});

test('web assets use forwarded https scheme behind the local ngrok proxy', function () {
    $response = $this
        ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
        ->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'dashboard.example.test',
        ])
        ->get('/');

    $response
        ->assertOk()
        ->assertSee('href="https://dashboard.example.test/build/assets/', false)
        ->assertDontSee('href="http://dashboard.example.test/build/assets/', false);
});

test('admin is redirected to the admin payment dashboard after login', function () {
    $admin = User::factory()->admin()->create();

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

test('dashboard header contains an accessible logout dropdown', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('data-profile-toggle', false)
        ->assertSee('data-profile-menu', false)
        ->assertSee('aria-expanded="false"', false)
        ->assertSee('action="'.route('logout').'"', false)
        ->assertSee('تسجيل الخروج');
});

test('restricted admin is redirected to the first page they can access', function () {
    $admin = User::factory()->restrictedAdmin()->create();
    $admin->givePermissionTo('doctors.view');

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.doctors'));
});

test('admin without permissions is redirected to the no access page', function () {
    $admin = User::factory()->restrictedAdmin()->create();

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.no-access'));
});

test('suspended users cannot start a web session', function () {
    $admin = User::factory()->admin()->create(['status' => 'suspended']);

    $this->from(route('login'))->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('doctor is redirected to the doctor payment dashboard after login', function () {
    $doctor = User::factory()->doctor()->create();

    $this->post(route('login.store'), [
        'email' => $doctor->email,
        'password' => 'password',
    ])->assertRedirect(route('web.doctor.dashboard'));

    $this->assertAuthenticatedAs($doctor);
});

test('invalid web credentials are rejected', function () {
    $user = User::factory()->create();

    $this->from(route('login'))->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'incorrect-password',
    ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('users outside the supported web roles cannot log in', function () {
    $user = User::factory()->create([
        'name' => 'Unsupported User',
        'email' => 'unsupported@example.test',
        'password' => Hash::make('password'),
    ]);
    $user->syncRoles([]);

    $this->from(route('login'))->post(route('login.store'), [
        'email' => 'unsupported@example.test',
        'password' => 'password',
    ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('authenticated user can log out of the web dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
