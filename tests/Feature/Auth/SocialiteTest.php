<?php

use App\Models\User;
use App\Services\BoardTemplateService;
use Database\Seeders\BoardTemplateSeeder;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

test('socialite callback creates a user and enters onboarding', function () {
    $this->seed(BoardTemplateSeeder::class);

    $socialUser = (new SocialiteUser)->map([
        'id' => 'google-123',
        'name' => 'Social User',
        'email' => 'social@example.com',
        'avatar' => null,
        'avatar_original' => null,
    ]);

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($socialUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect(route('onboarding.gate', absolute: false));
    $this->assertAuthenticated();

    $user = User::query()->where('email', 'social@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->provider)->toBe('google')
        ->and($user->provider_id)->toBe('google-123')
        ->and($user->settings)->not->toBeNull();
});

test('socialite callback logs in an existing linked user', function () {
    $this->seed(BoardTemplateSeeder::class);

    $user = User::factory()->create([
        'email' => 'existing@example.com',
        'provider' => 'google',
        'provider_id' => 'google-999',
        'preferred_name' => 'Existing',
    ]);
    app(BoardTemplateService::class)->copyToUser($user);
    $user->settings()->update([
        'voice_id' => 'device-default',
        'onboarding_completed_at' => now(),
    ]);

    $socialUser = (new SocialiteUser)->map([
        'id' => 'google-999',
        'name' => 'Existing',
        'email' => 'existing@example.com',
        'avatar' => null,
        'avatar_original' => null,
    ]);

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($socialUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $this->get('/auth/google/callback')
        ->assertRedirect(route('onboarding.gate', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('personalize page can be rendered with sso only', function () {
    $this->get('/personalize')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('auth/Personalize')
            ->has('providers', 3)
            ->missing('canResetPassword')
        );
});
