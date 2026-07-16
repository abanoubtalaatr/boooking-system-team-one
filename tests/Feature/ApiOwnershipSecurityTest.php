<?php

use App\Models\Favorite;
use App\Models\Patient;
use App\Models\Review;
use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('requires authentication for private favorites search history and review writes', function (): void {
    $this->getJson('/api/favorites')->assertUnauthorized();
    $this->postJson('/api/favorites', ['doctor_id' => 1])->assertUnauthorized();
    $this->deleteJson('/api/favorites', ['doctor_id' => 1])->assertUnauthorized();
    $this->getJson('/api/search-history')->assertUnauthorized();
    $this->deleteJson('/api/search-history/1')->assertUnauthorized();
    $this->postJson('/api/reviews')->assertUnauthorized();
    $this->patchJson('/api/reviews/1')->assertUnauthorized();
    $this->deleteJson('/api/reviews/1')->assertUnauthorized();
});

it('uses the authenticated patient as the favorite owner', function (): void {
    $patient = Patient::factory()->create();
    $otherPatient = Patient::factory()->create();
    $doctor = User::factory()->doctor()->create();
    Favorite::query()->create(['user_id' => $otherPatient->id, 'doctor_id' => $doctor->id]);

    Sanctum::actingAs($patient, ['*'], 'patient');

    $this->postJson('/api/favorites', ['doctor_id' => $doctor->id])->assertOk();

    expect(Favorite::query()->where('user_id', $patient->id)->where('doctor_id', $doctor->id)->exists())->toBeTrue();
    $this->getJson('/api/favorites')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('prevents a patient from changing another patients review', function (): void {
    $owner = Patient::factory()->create();
    $otherPatient = Patient::factory()->create();
    $doctor = User::factory()->doctor()->create();
    $review = Review::query()->create([
        'user_id' => $doctor->id,
        'patient_id' => $owner->id,
        'comment' => 'Original',
        'rating' => 4,
    ]);

    Sanctum::actingAs($otherPatient, ['*'], 'patient');

    $this->patchJson("/api/reviews/{$review->id}", ['comment' => 'Changed'])->assertForbidden();
    $this->deleteJson("/api/reviews/{$review->id}")->assertForbidden();
    expect($review->fresh()->comment)->toBe('Original');
});

it('limits search history to its authenticated user', function (): void {
    $user = Patient::factory()->create();
    $otherUser = Patient::factory()->create();
    SearchHistory::query()->create(['user_id' => $user->id, 'query' => 'Mine', 'source' => 'search']);
    SearchHistory::query()->create(['user_id' => $otherUser->id, 'query' => 'Theirs', 'source' => 'search']);

    Sanctum::actingAs($user, ['*'], 'patient');

    $this->getJson('/api/search-history')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.query', 'Mine');
});
