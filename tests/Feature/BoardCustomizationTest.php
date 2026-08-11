<?php

use App\Models\Menu;
use App\Models\Phrase;
use App\Models\User;
use App\Models\Word;
use App\Services\BoardTemplateService;
use Database\Seeders\BoardTemplateSeeder;
use Inertia\Testing\AssertableInertia as Assert;

function onboardedUser(string $name = 'Alex'): User
{
    $user = User::factory()->create([
        'preferred_name' => $name,
    ]);
    app(BoardTemplateService::class)->copyToUser($user);
    $user->settings()->update([
        'voice_id' => 'device-default',
        'onboarding_completed_at' => now(),
    ]);

    return $user;
}

test('template home word I speaks as eye', function () {
    $this->seed(BoardTemplateSeeder::class);

    $word = Word::query()->template()->whereNull('menu_id')->where('label', 'I')->firstOrFail();

    expect($word->speak_text)->toBe('eye')
        ->and($word->textToSpeak())->toBe('eye');
});

test('authenticated users can create a word with pronunciation', function () {
    $this->seed(BoardTemplateSeeder::class);
    $user = onboardedUser();

    $this->actingAs($user)
        ->from('/board')
        ->post(route('words.store'), [
            'label' => 'pizza',
            'speak_text' => 'peet-sah',
            'menu_id' => null,
        ])
        ->assertRedirect('/board');

    $word = Word::query()->forUser($user)->where('label', 'pizza')->firstOrFail();

    expect($word->speak_text)->toBe('peet-sah')
        ->and($word->textToSpeak())->toBe('peet-sah');
});

test('users cannot create duplicate word labels on their board', function () {
    $this->seed(BoardTemplateSeeder::class);
    $user = onboardedUser();

    $this->actingAs($user)
        ->from('/board')
        ->post(route('words.store'), [
            'label' => 'Want',
            'menu_id' => null,
        ])
        ->assertSessionHasErrors('label');
});

test('authenticated users can update word pronunciation', function () {
    $this->seed(BoardTemplateSeeder::class);
    $user = onboardedUser();

    $word = Word::query()->forUser($user)->whereNull('menu_id')->where('label', 'I')->firstOrFail();

    $this->actingAs($user)
        ->from('/board')
        ->put(route('words.update', $word), [
            'label' => 'I',
            'speak_text' => 'aye',
        ])
        ->assertRedirect('/board');

    expect($word->fresh()->speak_text)->toBe('aye');
});

test('authenticated users can hide built-in words but not delete them', function () {
    $this->seed(BoardTemplateSeeder::class);
    $user = onboardedUser();

    $word = Word::query()->forUser($user)->where('label', 'finished')->firstOrFail();

    expect($word->is_builtin)->toBeTrue();

    $this->actingAs($user)
        ->from('/board')
        ->delete(route('words.destroy', $word))
        ->assertForbidden();

    expect(Word::query()->whereKey($word->id)->exists())->toBeTrue();

    $this->actingAs($user)
        ->from('/board')
        ->post(route('words.hide', $word))
        ->assertRedirect('/board');

    expect($word->fresh()->is_hidden)->toBeTrue();

    $this->actingAs($user)
        ->from('/board')
        ->post(route('words.unhide', $word))
        ->assertRedirect('/board');

    expect($word->fresh()->is_hidden)->toBeFalse();
});

test('authenticated users can delete custom words', function () {
    $this->seed(BoardTemplateSeeder::class);
    $user = onboardedUser();

    $word = Word::query()->create([
        'user_id' => $user->id,
        'menu_id' => null,
        'label' => 'pizza',
        'sort_order' => 99,
        'is_builtin' => false,
        'is_hidden' => false,
    ]);

    $this->actingAs($user)
        ->from('/board')
        ->delete(route('words.destroy', $word))
        ->assertRedirect('/board');

    expect(Word::query()->whereKey($word->id)->exists())->toBeFalse();
});

test('authenticated users can reorder words on the home board', function () {
    $this->seed(BoardTemplateSeeder::class);
    $user = onboardedUser();

    $first = Word::query()->forUser($user)->whereNull('menu_id')->orderBy('sort_order')->firstOrFail();
    $second = Word::query()->forUser($user)->whereNull('menu_id')->where('sort_order', '>', $first->sort_order)->orderBy('sort_order')->firstOrFail();

    $firstOrder = $first->sort_order;
    $secondOrder = $second->sort_order;

    $this->actingAs($user)
        ->from('/board')
        ->post(route('words.move', $first), ['direction' => 'down'])
        ->assertRedirect('/board');

    expect($first->fresh()->sort_order)->toBe($secondOrder)
        ->and($second->fresh()->sort_order)->toBe($firstOrder);

    $labels = Word::query()
        ->forUser($user)
        ->whereNull('menu_id')
        ->orderBy('sort_order')
        ->pluck('label')
        ->all();

    expect($labels[0])->toBe($second->label)
        ->and($labels[1])->toBe($first->label);
});

test('authenticated users can reorder top-level folders', function () {
    $this->seed(BoardTemplateSeeder::class);
    $user = onboardedUser();

    $first = Menu::query()->forUser($user)->whereNull('parent_id')->orderBy('sort_order')->firstOrFail();
    $second = Menu::query()->forUser($user)->whereNull('parent_id')->where('sort_order', '>', $first->sort_order)->orderBy('sort_order')->firstOrFail();

    $firstOrder = $first->sort_order;
    $secondOrder = $second->sort_order;

    $this->actingAs($user)
        ->from('/board')
        ->post(route('menus.move', $first), ['direction' => 'down'])
        ->assertRedirect('/board');

    expect($first->fresh()->sort_order)->toBe($secondOrder)
        ->and($second->fresh()->sort_order)->toBe($firstOrder);
});

test('authenticated users can create rename and delete menus', function () {
    $this->seed(BoardTemplateSeeder::class);
    $user = onboardedUser();

    $this->actingAs($user)
        ->from('/board')
        ->post(route('menus.store'), [
            'name' => 'School',
            'parent_id' => null,
        ])
        ->assertRedirect('/board');

    $menu = Menu::query()->forUser($user)->where('name', 'School')->firstOrFail();

    $this->actingAs($user)
        ->put(route('menus.update', $menu), ['name' => 'Classroom'])
        ->assertRedirect();

    expect($menu->fresh()->name)->toBe('Classroom');

    Word::factory()->create([
        'user_id' => $user->id,
        'menu_id' => $menu->id,
        'label' => 'desk',
        'sort_order' => 1,
    ]);
    Phrase::factory()->create([
        'user_id' => $user->id,
        'menu_id' => $menu->id,
        'text' => 'I need my desk',
        'sort_order' => 1,
    ]);

    $this->actingAs($user)
        ->delete(route('menus.destroy', $menu))
        ->assertRedirect();

    expect(Menu::query()->whereKey($menu->id)->exists())->toBeFalse()
        ->and(Word::query()->forUser($user)->where('label', 'desk')->exists())->toBeFalse()
        ->and(Phrase::query()->forUser($user)->where('text', 'I need my desk')->exists())->toBeFalse();
});

test('guests cannot customize the board', function () {
    $this->seed(BoardTemplateSeeder::class);

    $this->post(route('words.store'), [
        'label' => 'pizza',
        'menu_id' => null,
    ])->assertRedirect('/personalize');

    $this->post(route('menus.store'), [
        'name' => 'School',
        'parent_id' => null,
    ])->assertRedirect('/personalize');
});

test('users cannot edit another users words', function () {
    $this->seed(BoardTemplateSeeder::class);

    $owner = onboardedUser('Owner');
    $intruder = onboardedUser('Intruder');

    $word = Word::query()->forUser($owner)->where('label', 'want')->firstOrFail();

    $this->actingAs($intruder)
        ->put(route('words.update', $word), [
            'label' => 'stolen',
            'speak_text' => null,
        ])
        ->assertForbidden();
});

test('board words expose nullable speak_text for editing', function () {
    $this->seed(BoardTemplateSeeder::class);
    $user = onboardedUser();

    $this->actingAs($user)
        ->get('/board')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->has('words')
            ->where('words.0.label', 'I')
            ->where('words.0.speak_text', 'eye')
        );
});
