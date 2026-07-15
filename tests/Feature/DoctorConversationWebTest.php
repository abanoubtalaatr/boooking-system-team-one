<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('shows only the authenticated doctor conversations', function (): void {
    $this->withoutVite();
    $doctor = User::factory()->create(['role' => 'doctor']);
    $otherDoctor = User::factory()->create(['role' => 'doctor']);
    $patient = Patient::factory()->create(['name' => 'Own Patient']);
    $otherPatient = Patient::factory()->create(['name' => 'Other Patient']);
    $conversation = Conversation::factory()->create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
    ]);
    Conversation::factory()->create([
        'doctor_id' => $otherDoctor->id,
        'patient_id' => $otherPatient->id,
    ]);
    Message::factory()->fromPatient($patient)->create([
        'conversation_id' => $conversation->id,
        'body' => 'Private consultation message',
    ]);

    $this->actingAs($doctor)
        ->get(route('doctor.conversations'))
        ->assertSuccessful()
        ->assertSee('Own Patient')
        ->assertSee('Private consultation message')
        ->assertDontSee('Other Patient');
});

it('forbids a doctor from opening another doctor conversation', function (): void {
    $doctor = User::factory()->create(['role' => 'doctor']);
    $otherDoctor = User::factory()->create(['role' => 'doctor']);
    $conversation = Conversation::factory()->create([
        'doctor_id' => $otherDoctor->id,
    ]);

    $this->actingAs($doctor)
        ->get(route('doctor.conversations.show', $conversation))
        ->assertForbidden();
});
