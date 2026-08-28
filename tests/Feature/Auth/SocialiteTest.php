<?php

use App\Models\User;
use App\Services\BoardTemplateService;
use Database\Seeders\BoardTemplateSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
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
            ->where('providers', ['google', 'apple'])
            ->missing('canResetPassword')
        );
});

test('facebook login routes are not available', function (string $path) {
    $this->get($path)->assertNotFound();
})->with([
    '/auth/facebook/redirect',
    '/auth/facebook/callback',
]);

test('native google redirect remembers capacitor oauth', function () {
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $this->get('/auth/google/redirect?native=1')
        ->assertRedirect('https://accounts.google.com/o/oauth2/auth');

    expect(session('capacitor_oauth'))->toBeTrue();
});

test('socialite callback for the android app returns a talkie deep link', function () {
    $this->seed(BoardTemplateSeeder::class);

    $socialUser = (new SocialiteUser)->map([
        'id' => 'google-native',
        'name' => 'Native User',
        'email' => 'native@example.com',
        'avatar' => null,
        'avatar_original' => null,
    ]);

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($socialUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->withSession(['capacitor_oauth' => true])
        ->get('/auth/google/callback');

    $location = $response->headers->get('Location');

    expect($location)->toStartWith('talkie://auth?token=');

    $token = (string) parse_url((string) $location, PHP_URL_QUERY);
    parse_str($token, $query);

    expect($query['token'] ?? null)->toBeString()->toHaveLength(64);
    $this->assertAuthenticated();
});

test('native handoff logs the user into the webview session', function () {
    $user = createOnboardedUser();
    $token = Str::random(64);

    Cache::put('capacitor-auth:'.$token, $user->id, now()->addMinutes(2));

    $this->get('/auth/native/handoff?token='.$token)
        ->assertRedirect(route('onboarding.gate', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('native handoff rejects missing invalid and reused tokens', function () {
    $user = createOnboardedUser();
    $token = Str::random(64);

    $this->get('/auth/native/handoff?token=short')->assertForbidden();
    $this->get('/auth/native/handoff?token='.str_repeat('a', 64))->assertForbidden();

    Cache::put('capacitor-auth:'.$token, $user->id, now()->addMinutes(2));

    $this->get('/auth/native/handoff?token='.$token)
        ->assertRedirect(route('onboarding.gate', absolute: false));

    $this->get('/auth/native/handoff?token='.$token)->assertForbidden();
});
