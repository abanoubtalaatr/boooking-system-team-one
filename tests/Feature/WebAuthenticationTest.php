<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
    $admin = User::factory()->create(['role' => 'admin']);

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('web.admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

test('doctor is redirected to the doctor payment dashboard after login', function () {
    $doctor = User::factory()->create(['role' => 'doctor']);

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
    DB::table('users')->insert([
        'name' => 'Unsupported User',
        'email' => 'unsupported@example.test',
        'password' => Hash::make('password'),
        'role' => 'patient',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

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
