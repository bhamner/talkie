<?php

use App\Models\User;
use App\Services\BoardTemplateService;
use Database\Seeders\BoardTemplateSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('name onboarding can be completed', function () {
    $user = User::factory()->create([
        'preferred_name' => null,
    ]);

    $this->actingAs($user)
        ->put('/onboarding/name', [
            'preferred_name' => 'Jordan',
        ])
        ->assertRedirect(route('onboarding.voice', absolute: false));

    expect($user->fresh()->preferred_name)->toBe('Jordan');
});

test('voice onboarding completes personalization', function () {
    $this->seed(BoardTemplateSeeder::class);

    $user = User::factory()->create([
        'preferred_name' => 'Jordan',
    ]);
    app(BoardTemplateService::class)->copyToUser($user);

    $this->actingAs($user)
        ->get('/onboarding/voice')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('onboarding/Voice')
            ->has('voices')
        );

    $this->actingAs($user)
        ->put('/onboarding/voice', [
            'voice_id' => 'device-default',
            'voice_uri' => 'device-uri',
            'voice_name' => 'System',
        ])
        ->assertRedirect(route('board', absolute: false));

    $user->refresh();

    expect($user->hasCompletedOnboarding())->toBeTrue()
        ->and($user->settings->voice_id)->toBe('device-default')
        ->and($user->settings->onboarding_completed_at)->not->toBeNull();
});

test('native app voice onboarding defaults to piper', function () {
    $user = User::factory()->create([
        'preferred_name' => 'Jordan',
    ]);

    $this->actingAs($user)
        ->withHeaders([
            'User-Agent' => 'Mozilla/5.0 TalkieNative/android',
        ])
        ->get('/onboarding/voice')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('onboarding/Voice')
            ->where('voice.id', 'premium-nova')
            ->where('voice.engine', 'piper')
        );
});

test('onboarding gate sends users to the correct step', function () {
    $user = User::factory()->create([
        'preferred_name' => null,
    ]);

    $this->actingAs($user)
        ->get('/onboarding')
        ->assertRedirect(route('onboarding.name', absolute: false));
});
