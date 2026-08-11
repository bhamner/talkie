<?php

use App\Models\Menu;
use App\Models\Phrase;
use App\Models\User;
use App\Models\Word;
use App\Services\BoardTemplateService;
use Database\Seeders\BoardTemplateSeeder;

test('template word labels are unique across all menus', function () {
    $this->seed(BoardTemplateSeeder::class);

    $labels = Word::query()
        ->template()
        ->pluck('label')
        ->map(fn (string $label) => strtolower($label));

    expect($labels->duplicates()->values()->all())->toBe([]);
});

test('copying the template includes colors shapes and numbers', function () {
    $this->seed(BoardTemplateSeeder::class);

    $user = User::factory()->create();
    app(BoardTemplateService::class)->copyToUser($user);

    $menuNames = $user->menus()->whereNull('parent_id')->orderBy('sort_order')->pluck('name')->all();

    expect($menuNames)->toContain('Colors', 'Shapes', 'Numbers')
        ->and(Word::query()->forUser($user)->whereHas('menu', fn ($query) => $query->where('name', 'Colors'))->count())->toBe(10)
        ->and(Word::query()->forUser($user)->whereHas('menu', fn ($query) => $query->where('name', 'Shapes'))->count())->toBe(8)
        ->and(Word::query()->forUser($user)->whereHas('menu', fn ($query) => $query->where('name', 'Numbers'))->count())->toBe(11)
        ->and(Phrase::query()->forUser($user)->whereHas('menu', fn ($query) => $query->where('name', 'Food'))->where('text', 'I am hungry')->exists())->toBeTrue()
        ->and(Phrase::query()->forUser($user)->whereNull('menu_id')->count())->toBeGreaterThan(0);

    $one = Word::query()->forUser($user)->where('label', '1')->firstOrFail();

    expect($one->textToSpeak())->toBe('one');
});

test('sync adds new template categories to an existing user board', function () {
    $this->seed(BoardTemplateSeeder::class);

    $user = User::factory()->create();
    app(BoardTemplateService::class)->copyToUser($user);

    Menu::query()->forUser($user)->where('name', 'Colors')->delete();
    Menu::query()->forUser($user)->where('name', 'Shapes')->delete();
    Menu::query()->forUser($user)->where('name', 'Numbers')->delete();

    expect($user->menus()->whereNull('parent_id')->whereIn('name', ['Colors', 'Shapes', 'Numbers'])->count())->toBe(0);

    app(BoardTemplateService::class)->syncMissingCategoriesToUser($user);

    expect($user->menus()->whereNull('parent_id')->whereIn('name', ['Colors', 'Shapes', 'Numbers'])->count())->toBe(3)
        ->and($user->menus()->whereNull('parent_id')->where('name', 'Food')->count())->toBe(1);
});
