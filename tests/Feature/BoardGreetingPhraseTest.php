<?php

use App\Models\Menu;
use App\Models\User;
use App\Services\BoardTemplateService;
use Database\Seeders\BoardTemplateSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('home board includes greeting phrase for users with preferred name', function () {
    $this->seed(BoardTemplateSeeder::class);

    $user = User::factory()->create([
        'preferred_name' => 'Alex',
    ]);
    app(BoardTemplateService::class)->copyToUser($user);
    $user->settings()->update([
        'voice_id' => 'device-default',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->get('/board')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->where('phrases.0.id', 'greeting')
            ->where('phrases.0.text', 'Hello, my name is Alex')
            ->where('phrases.0.is_greeting', true)
            ->where('can_edit', true)
        );
});

test('guests do not receive greeting phrase on home board', function () {
    $this->seed(BoardTemplateSeeder::class);

    $this->get('/board')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->where('can_edit', false)
            ->where('phrases.0.is_greeting', false)
            ->where('phrases.0.text', 'I need help')
        );
});

test('nested menus do not include greeting phrase', function () {
    $this->seed(BoardTemplateSeeder::class);

    $user = User::factory()->create([
        'preferred_name' => 'Alex',
    ]);
    app(BoardTemplateService::class)->copyToUser($user);
    $user->settings()->update([
        'voice_id' => 'device-default',
        'onboarding_completed_at' => now(),
    ]);

    $food = Menu::query()->forUser($user)->where('name', 'Food')->firstOrFail();

    $this->actingAs($user)
        ->get('/board/'.$food->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->where('phrases.0.is_greeting', false)
            ->where('phrases.0.text', 'I am hungry')
        );
});
