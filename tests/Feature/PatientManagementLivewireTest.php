<?php

use App\Livewire\Admin\PatientManager;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('searches patients by name phone and email with livewire', function (): void {
    $admin = User::factory()->restrictedAdmin()->create();
    $admin->givePermissionTo('patients.view');
    $ahmed = Patient::factory()->create(['name' => 'أحمد محمد', 'phone' => '01011111111', 'email' => 'ahmed@example.test']);
    $mona = Patient::factory()->create(['name' => 'منى علي', 'phone' => '01122222222', 'email' => 'mona@example.test']);

    Livewire::actingAs($admin)->test(PatientManager::class)
        ->set('search', 'أحمد')
        ->assertSee($ahmed->email)
        ->assertDontSee($mona->email)
        ->set('search', '011222')
        ->assertSee($mona->email)
        ->assertDontSee($ahmed->email)
        ->set('search', 'ahmed@example')
        ->assertSee($ahmed->phone)
        ->assertDontSee($mona->phone);
});

it('creates a complete patient account', function (): void {
    $admin = User::factory()->restrictedAdmin()->create();
    $admin->givePermissionTo(['patients.view', 'patients.create']);

    Livewire::actingAs($admin)->test(PatientManager::class)
        ->call('create')
        ->set('form.name', 'مريض جديد')
        ->set('form.phone', '01099999999')
        ->set('form.email', 'new.patient@example.test')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->set('form.birthdate', '1995-05-10')
        ->set('form.latitude', '30.0444000')
        ->set('form.longitude', '31.2357000')
        ->set('form.verified', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('تم إنشاء حساب المريض بنجاح.');

    $patient = Patient::query()->where('email', 'new.patient@example.test')->firstOrFail();
    expect($patient->name)->toBe('مريض جديد')
        ->and($patient->isVerified())->toBeTrue()
        ->and(Hash::check('password123', $patient->password))->toBeTrue();
});

it('updates an existing patient without replacing an empty password', function (): void {
    $admin = User::factory()->restrictedAdmin()->create();
    $admin->givePermissionTo(['patients.view', 'patients.update']);
    $patient = Patient::factory()->create(['name' => 'الاسم القديم']);
    $originalPassword = $patient->password;

    Livewire::actingAs($admin)->test(PatientManager::class)
        ->call('edit', $patient->id)
        ->set('form.name', 'الاسم الجديد')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('تم تحديث بيانات المريض بنجاح.');

    expect($patient->refresh()->name)->toBe('الاسم الجديد')
        ->and($patient->password)->toBe($originalPassword);
});

it('soft deletes a patient and revokes their tokens', function (): void {
    $admin = User::factory()->restrictedAdmin()->create();
    $admin->givePermissionTo(['patients.view', 'patients.delete']);
    $patient = Patient::factory()->create();
    $patient->createToken('mobile');

    Livewire::actingAs($admin)->test(PatientManager::class)
        ->call('delete', $patient->id)
        ->assertSee('تم حذف حساب المريض بنجاح.');

    expect(Patient::query()->find($patient->id))->toBeNull()
        ->and(Patient::withTrashed()->find($patient->id)?->trashed())->toBeTrue()
        ->and($patient->tokens()->count())->toBe(0);
});

it('denies patient mutations without their operation permissions', function (): void {
    $admin = User::factory()->restrictedAdmin()->create();
    $admin->givePermissionTo('patients.view');
    $patient = Patient::factory()->create();

    Livewire::actingAs($admin)->test(PatientManager::class)->call('create')->assertForbidden();
    Livewire::actingAs($admin)->test(PatientManager::class)->call('edit', $patient->id)->assertForbidden();
    Livewire::actingAs($admin)->test(PatientManager::class)->call('delete', $patient->id)->assertForbidden();
});
