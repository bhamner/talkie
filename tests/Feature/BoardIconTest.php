<?php

use App\Models\Menu;
use App\Models\User;
use App\Models\Word;
use App\Services\BoardTemplateService;
use App\Support\BoardIcons;
use Database\Seeders\BoardTemplateSeeder;

test('template words and folders store icon keys on the row', function () {
    $this->seed(BoardTemplateSeeder::class);

    $apple = Word::query()->template()->where('label', 'apple')->firstOrFail();
    $homeWord = Word::query()->template()->whereNull('menu_id')->where('label', 'I')->firstOrFail();
    $food = Menu::query()->template()->whereNull('parent_id')->where('name', 'Food')->firstOrFail();
    $joiners = Menu::query()->template()->whereNull('parent_id')->where('name', 'Joiners')->firstOrFail();

    expect($apple->icon)->toBe('Apple01Icon')
        ->and($homeWord->icon)->toBe('lucide:smile')
        ->and($food->icon)->toBe('ServingFoodIcon')
        ->and($joiners->icon)->toBeNull()
        ->and(BoardIcons::forWord('golf cart'))->toBe('GolfCartIcon');
});

test('copying the template copies icon keys onto the user board', function () {
    $this->seed(BoardTemplateSeeder::class);

    $user = User::factory()->create();
    app(BoardTemplateService::class)->copyToUser($user);

    $apple = Word::query()->forUser($user)->where('label', 'apple')->firstOrFail();
    $food = Menu::query()->forUser($user)->where('name', 'Food')->firstOrFail();

    expect($apple->icon)->toBe('Apple01Icon')
        ->and($food->icon)->toBe('ServingFoodIcon');
});

test('sync copies missing icons onto an existing user board', function () {
    $this->seed(BoardTemplateSeeder::class);

    $user = User::factory()->create();
    app(BoardTemplateService::class)->copyToUser($user);

    Word::query()->forUser($user)->where('label', 'apple')->update(['icon' => null]);
    Menu::query()->forUser($user)->where('name', 'Food')->update(['icon' => null]);

    app(BoardTemplateService::class)->syncMissingCategoriesToUser($user);

    expect(Word::query()->forUser($user)->where('label', 'apple')->value('icon'))->toBe('Apple01Icon')
        ->and(Menu::query()->forUser($user)->where('name', 'Food')->value('icon'))->toBe('ServingFoodIcon');
});

test('board page includes icon keys for words and folders', function () {
    $this->seed(BoardTemplateSeeder::class);

    $this->get('/board')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('board/Show')
            ->where('words', fn ($words) => collect($words)->contains(fn ($word) => $word['label'] === 'I' && $word['icon'] === 'lucide:smile'))
            ->where('menus', fn ($menus) => collect($menus)->contains(fn ($menu) => $menu['name'] === 'Food' && $menu['icon'] === 'ServingFoodIcon'))
        );
});
