<?php

use App\Models\Menu;
use App\Models\User;
use App\Models\Word;
use Database\Seeders\BoardTemplateSeeder;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register and enter onboarding', function () {
    $this->seed(BoardTemplateSeeder::class);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('onboarding.gate', absolute: false));

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();

    expect(Menu::query()->forUser($user)->count())->toBeGreaterThan(0)
        ->and(Word::query()->forUser($user)->count())->toBeGreaterThan(0)
        ->and($user->settings)->not->toBeNull()
        ->and($user->hasCompletedOnboarding())->toBeFalse();
});
