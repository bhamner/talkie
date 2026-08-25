<?php

use App\Models\Menu;
use App\Models\User;
use App\Services\BoardTemplateService;
use Database\Seeders\BoardTemplateSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('folder urls use the folder name instead of the id', function () {
    $this->seed(BoardTemplateSeeder::class);

    $joiners = Menu::query()->template()->whereNull('parent_id')->where('name', 'Joiners')->firstOrFail();

    expect($joiners->slug)->toBe('joiners');

    $this->get('/board/joiners')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->where('menu.name', 'Joiners')
            ->where('menu.slug', 'joiners')
        );
});

test('numeric folder urls redirect to the pretty slug', function () {
    $this->seed(BoardTemplateSeeder::class);

    $joiners = Menu::query()->template()->whereNull('parent_id')->where('name', 'Joiners')->firstOrFail();

    $this->get('/board/'.$joiners->id)
        ->assertRedirect('/board/joiners');
});

test('nested folders use a name slug', function () {
    $this->seed(BoardTemplateSeeder::class);

    $drinks = Menu::query()->template()->where('name', 'Drinks')->firstOrFail();

    expect($drinks->slug)->toBe('drinks');

    $this->get('/board/drinks')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->where('menu.name', 'Drinks')
            ->where('menu.slug', 'drinks')
        );
});

test('where and when folders use a hyphenated slug', function () {
    $this->seed(BoardTemplateSeeder::class);

    $this->get('/board/where-when')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->where('menu.name', 'Where & when')
        );
});

test('numeric folder urls keep the highlight query when redirecting', function () {
    $this->seed(BoardTemplateSeeder::class);

    $food = Menu::query()->template()->whereNull('parent_id')->where('name', 'Food')->firstOrFail();
    $cookie = $food->words()->where('label', 'cookie')->firstOrFail();

    $this->get('/board/'.$food->id.'?highlight=word-'.$cookie->id)
        ->assertRedirect('/board/food?highlight=word-'.$cookie->id);
});

test('unknown folder slugs return not found', function () {
    $this->seed(BoardTemplateSeeder::class);

    $this->get('/board/not-a-folder')->assertNotFound();
});

test('duplicate folder names get a numbered slug', function () {
    $this->seed(BoardTemplateSeeder::class);
    $user = User::factory()->create([
        'preferred_name' => 'Alex',
    ]);
    app(BoardTemplateService::class)->copyToUser($user);
    $user->settings()->update([
        'voice_id' => 'device-default',
        'onboarding_completed_at' => now(),
    ]);

    $first = Menu::query()->create([
        'user_id' => $user->id,
        'name' => 'School',
        'parent_id' => null,
        'sort_order' => 100,
        'is_builtin' => false,
        'is_hidden' => false,
    ]);
    $second = Menu::query()->create([
        'user_id' => $user->id,
        'name' => 'School',
        'parent_id' => null,
        'sort_order' => 101,
        'is_builtin' => false,
        'is_hidden' => false,
    ]);

    expect($first->slug)->toBe('school')
        ->and($second->slug)->toBe('school-2');

    $this->actingAs($user)
        ->get('/board/school')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('menu.id', $first->id)
        );

    $this->actingAs($user)
        ->get('/board/school-2')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('menu.id', $second->id)
        );
});

test('renaming a folder updates its slug', function () {
    $this->seed(BoardTemplateSeeder::class);
    $user = User::factory()->create([
        'preferred_name' => 'Alex',
    ]);
    app(BoardTemplateService::class)->copyToUser($user);
    $user->settings()->update([
        'voice_id' => 'device-default',
        'onboarding_completed_at' => now(),
    ]);

    $menu = Menu::query()->forUser($user)->where('name', 'Joiners')->firstOrFail();
    $menu->update(['name' => 'Connectors']);

    expect($menu->fresh()->slug)->toBe('connectors');

    $this->actingAs($user)
        ->get('/board/connectors')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('menu.name', 'Connectors')
        );

    $this->actingAs($user)
        ->get('/board/joiners')
        ->assertNotFound();
});
