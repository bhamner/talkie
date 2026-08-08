<?php

use App\Models\Menu;
use App\Models\User;
use App\Services\BoardTemplateService;
use Database\Seeders\BoardTemplateSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can view the shared board', function () {
    $this->seed(BoardTemplateSeeder::class);

    $this->get('/board')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->where('is_guest', true)
            ->where('menu', null)
            ->has('menus')
            ->has('words')
        );
});

test('guests can open a nested template menu', function () {
    $this->seed(BoardTemplateSeeder::class);

    $menu = Menu::query()->template()->whereNull('parent_id')->where('name', 'Food')->firstOrFail();

    $this->get('/board/'.$menu->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->where('is_guest', true)
            ->where('menu.name', 'Food')
        );
});

test('authenticated users with completed onboarding see their board and greeting name', function () {
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
            ->where('is_guest', false)
            ->where('preferred_name', 'Alex')
        );
});

test('authenticated users with incomplete onboarding are redirected from the board', function () {
    $user = User::factory()->create([
        'preferred_name' => null,
    ]);

    $this->actingAs($user)
        ->get('/board')
        ->assertRedirect(route('onboarding.name', absolute: false));
});
