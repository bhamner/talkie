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
            ->has('menus', 8)
            ->has('words')
            ->where('menus.5.name', 'Colors')
            ->where('menus.6.name', 'Shapes')
            ->where('menus.7.name', 'Numbers')
        );
});

test('guests can open colors shapes and numbers menus', function (string $name) {
    $this->seed(BoardTemplateSeeder::class);

    $menu = Menu::query()->template()->whereNull('parent_id')->where('name', $name)->firstOrFail();

    $this->get('/board/'.$menu->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->where('menu.name', $name)
            ->has('words')
        );
})->with(['Colors', 'Shapes', 'Numbers']);

test('authenticated users receive new template categories on the board', function () {
    $this->seed(BoardTemplateSeeder::class);

    $user = User::factory()->create([
        'preferred_name' => 'Alex',
    ]);
    app(BoardTemplateService::class)->copyToUser($user);
    $user->settings()->update([
        'voice_id' => 'device-default',
        'onboarding_completed_at' => now(),
    ]);

    Menu::query()->forUser($user)->where('name', 'Colors')->delete();

    $this->actingAs($user)
        ->get('/board')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->where('is_guest', false)
        );

    expect(Menu::query()->forUser($user)->where('name', 'Colors')->exists())->toBeTrue();
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
