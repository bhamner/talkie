<?php

use App\Models\Phrase;
use App\Models\User;
use App\Models\Word;
use App\Services\BoardTemplateService;
use Database\Seeders\BoardTemplateSeeder;

function visibilityUser(): User
{
    $user = User::factory()->create([
        'preferred_name' => 'Alex',
    ]);
    app(BoardTemplateService::class)->copyToUser($user);
    $user->settings()->update([
        'voice_id' => 'device-default',
        'onboarding_completed_at' => now(),
    ]);

    return $user;
}

test('copied template words and phrases are marked built-in', function () {
    $this->seed(BoardTemplateSeeder::class);
    $user = visibilityUser();

    expect(Word::query()->forUser($user)->where('label', 'want')->value('is_builtin'))->toBeTrue()
        ->and(Phrase::query()->forUser($user)->where('text', 'I need help')->value('is_builtin'))->toBeTrue()
        ->and(Word::query()->forUser($user)->where('label', 'want')->value('is_hidden'))->toBeFalse();
});

test('hidden words are omitted from the playable board payload visibility flag', function () {
    $this->seed(BoardTemplateSeeder::class);
    $user = visibilityUser();

    $word = Word::query()->forUser($user)->whereNull('menu_id')->where('label', 'want')->firstOrFail();
    $word->update(['is_hidden' => true]);

    $this->actingAs($user)
        ->get('/board')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('board/Show')
            ->where('words', fn ($words) => collect($words)->contains(fn ($item) => $item['label'] === 'want' && $item['is_hidden'] === true))
        );
});

test('authenticated users can hide and unhide built-in phrases', function () {
    $this->seed(BoardTemplateSeeder::class);
    $user = visibilityUser();

    $phrase = Phrase::query()->forUser($user)->whereNull('menu_id')->where('text', 'I need help')->firstOrFail();

    $this->actingAs($user)
        ->from('/board')
        ->post(route('phrases.hide', $phrase))
        ->assertRedirect('/board');

    expect($phrase->fresh()->is_hidden)->toBeTrue();

    $this->actingAs($user)
        ->from('/board')
        ->delete(route('phrases.destroy', $phrase))
        ->assertForbidden();

    $this->actingAs($user)
        ->from('/board')
        ->post(route('phrases.unhide', $phrase))
        ->assertRedirect('/board');

    expect($phrase->fresh()->is_hidden)->toBeFalse();
});

test('authenticated users can delete custom phrases', function () {
    $this->seed(BoardTemplateSeeder::class);
    $user = visibilityUser();

    $phrase = Phrase::query()->create([
        'user_id' => $user->id,
        'menu_id' => null,
        'text' => 'Custom snack please',
        'sort_order' => 99,
        'is_builtin' => false,
        'is_hidden' => false,
    ]);

    $this->actingAs($user)
        ->from('/board')
        ->delete(route('phrases.destroy', $phrase))
        ->assertRedirect('/board');

    expect(Phrase::query()->whereKey($phrase->id)->exists())->toBeFalse();
});
