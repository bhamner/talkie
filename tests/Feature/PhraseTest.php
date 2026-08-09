<?php

use App\Models\Menu;
use App\Models\Phrase;
use App\Models\User;
use App\Services\BoardTemplateService;
use Database\Seeders\BoardTemplateSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('board pages include phrases for the current menu', function () {
    $this->seed(BoardTemplateSeeder::class);

    $food = Menu::query()->template()->whereNull('parent_id')->where('name', 'Food')->firstOrFail();

    $this->get('/board/'.$food->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->has('phrases')
            ->where('phrases.0.text', 'I am hungry')
            ->where('phrases.1.text', 'I am thirsty')
        );
});

test('authenticated users can create a phrase for a menu', function () {
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
        ->from('/board/'.$food->id)
        ->post(route('phrases.store'), [
            'text' => 'I want pizza',
            'menu_id' => $food->id,
        ])
        ->assertRedirect('/board/'.$food->id);

    expect(Phrase::query()->forUser($user)->where('menu_id', $food->id)->where('text', 'I want pizza')->exists())->toBeTrue();
});

test('authenticated users can create a home phrase', function () {
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
        ->from('/board')
        ->post(route('phrases.store'), [
            'text' => 'I need a break',
            'menu_id' => null,
        ])
        ->assertRedirect('/board');

    expect(Phrase::query()->forUser($user)->whereNull('menu_id')->where('text', 'I need a break')->exists())->toBeTrue();
});

test('guests cannot create phrases', function () {
    $this->seed(BoardTemplateSeeder::class);

    $food = Menu::query()->template()->where('name', 'Food')->firstOrFail();

    $this->post(route('phrases.store'), [
        'text' => 'I want pizza',
        'menu_id' => $food->id,
    ])->assertRedirect('/personalize');
});

test('users cannot create phrases on another users menu', function () {
    $this->seed(BoardTemplateSeeder::class);

    $owner = User::factory()->create(['preferred_name' => 'Owner']);
    app(BoardTemplateService::class)->copyToUser($owner);
    $owner->settings()->update([
        'voice_id' => 'device-default',
        'onboarding_completed_at' => now(),
    ]);

    $intruder = User::factory()->create(['preferred_name' => 'Intruder']);
    app(BoardTemplateService::class)->copyToUser($intruder);
    $intruder->settings()->update([
        'voice_id' => 'device-default',
        'onboarding_completed_at' => now(),
    ]);

    $ownerFood = Menu::query()->forUser($owner)->where('name', 'Food')->firstOrFail();

    $this->actingAs($intruder)
        ->post(route('phrases.store'), [
            'text' => 'Stolen phrase',
            'menu_id' => $ownerFood->id,
        ])
        ->assertSessionHasErrors('menu_id');

    expect(Phrase::query()->where('text', 'Stolen phrase')->exists())->toBeFalse();
});

test('sync adds missing template phrases to existing menus', function () {
    $this->seed(BoardTemplateSeeder::class);

    $user = User::factory()->create(['preferred_name' => 'Alex']);
    app(BoardTemplateService::class)->copyToUser($user);
    $user->settings()->update([
        'voice_id' => 'device-default',
        'onboarding_completed_at' => now(),
    ]);

    $food = Menu::query()->forUser($user)->where('name', 'Food')->firstOrFail();
    Phrase::query()->forUser($user)->where('menu_id', $food->id)->delete();

    $this->actingAs($user)->get('/board/'.$food->id)->assertOk();

    expect(Phrase::query()->forUser($user)->where('menu_id', $food->id)->where('text', 'I am hungry')->exists())->toBeTrue();
});
