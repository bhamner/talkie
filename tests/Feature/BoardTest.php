<?php

use App\Models\Menu;
use App\Models\User;
use App\Services\BoardTemplateService;
use Database\Seeders\BoardTemplateSeeder;
use Illuminate\Support\Facades\DB;
use Inertia\Support\Header;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can view the shared board', function () {
    $this->seed(BoardTemplateSeeder::class);

    $this->get('/board')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->where('is_guest', true)
            ->where('menu', null)
            ->has('menus', 24)
            ->has('words')
            ->has('search_index.menus')
            ->has('search_index.words')
            ->where('menus.21.name', 'Colors')
            ->where('menus.22.name', 'Shapes')
            ->where('menus.23.name', 'Numbers')
            ->where('voice.provider', 'device')
        );
});

test('native app guests receive piper as the board voice', function () {
    $this->seed(BoardTemplateSeeder::class);

    $this->withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36 TalkieNative/android',
    ])->get('/board')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->where('is_guest', true)
            ->where('voice.id', 'premium-nova')
            ->where('voice.provider', 'bundled')
            ->where('voice.engine', 'piper')
            ->where('voice.model', 'en_US-libritts_r-medium')
        );
});

test('board document requests safe-area insets for native shells', function () {
    $this->seed(BoardTemplateSeeder::class);

    $this->get('/board')
        ->assertOk()
        ->assertSee('viewport-fit=cover', false)
        ->assertSee('<meta name="color-scheme" content="light">', false);
});

test('phrase bar stays put while the board tiles scroll', function () {
    $show = file_get_contents(resource_path('js/pages/board/Show.vue'));
    $layout = file_get_contents(resource_path('js/layouts/app/AppFullscreenLayout.vue'));

    expect($show)->toContain('shrink-0')
        ->and($show)->toContain('overflow-y-auto overscroll-y-contain touch-pan-y')
        ->and($show)->not->toContain('top-[4.25rem]')
        ->and($layout)->not->toContain("pb-[max(5rem,env(safe-area-inset-bottom))]")
        ->and($layout)->toContain('overflow-hidden')
        ->and($layout)->toContain('pt-[10px]');
});

test('mobile board chrome hides the footer and keeps phrase controls on one line', function () {
    $show = file_get_contents(resource_path('js/pages/board/Show.vue'));
    $layout = file_get_contents(resource_path('js/layouts/app/AppFullscreenLayout.vue'));
    $phrases = file_get_contents(resource_path('js/components/PhrasesPanel.vue'));

    expect($layout)->toContain("v-if=\"!user && isSmUp\"")
        ->and($layout)->toContain('(min-width: 640px)')
        ->and($layout)->toContain('px-3 py-4')
        ->and($layout)->not->toContain('md:block')
        ->and($layout)->not->toContain('landscape:hidden')
        ->and($show)->toContain('flex flex-nowrap items-center justify-between')
        ->and($show)->toContain('flex min-w-0 flex-nowrap')
        ->and($show)->toContain('h-8 shrink-0')
        ->and($show)->toContain('flex flex-nowrap items-center justify-end')
        ->and($show)->toContain('h-7 min-w-7 shrink-0')
        ->and($phrases)->toContain('h-8 shrink-0')
        ->and($layout)->toContain('hidden sm:inline')
        ->and($layout)->toContain('aria-label="Personalize"')
        ->and($show)->toContain('hidden items-center justify-center gap-2 px-3 py-4')
        ->and($show)->toContain('sm:flex');
});

test('board search highlight query is passed through to the page', function () {
    $this->seed(BoardTemplateSeeder::class);

    $food = Menu::query()->template()->whereNull('parent_id')->where('name', 'Food')->firstOrFail();
    $cookie = $food->words()->where('label', 'cookie')->firstOrFail();

    $this->get('/board/'.$food->slug.'?highlight=word-'.$cookie->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->where('highlight', 'word-'.$cookie->id)
            ->where('menu.name', 'Food')
        );
});
test('guests can open colors shapes and numbers menus', function (string $name) {
    $this->seed(BoardTemplateSeeder::class);

    $menu = Menu::query()->template()->whereNull('parent_id')->where('name', $name)->firstOrFail();

    $this->get('/board/'.$menu->slug)
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

test('nested board visits do not restore missing template categories', function () {
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

    $food = Menu::query()->forUser($user)->whereNull('parent_id')->where('name', 'Food')->firstOrFail();

    $this->actingAs($user)
        ->get('/board/'.$food->slug)
        ->assertOk();

    expect(Menu::query()->forUser($user)->where('name', 'Colors')->exists())->toBeFalse();

    $this->actingAs($user)
        ->get('/board')
        ->assertOk();

    expect(Menu::query()->forUser($user)->where('name', 'Colors')->exists())->toBeTrue();
});

test('guests can open a nested template menu', function () {
    $this->seed(BoardTemplateSeeder::class);

    $menu = Menu::query()->template()->whereNull('parent_id')->where('name', 'Food')->firstOrFail();

    $this->get('/board/'.$menu->slug)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->where('is_guest', true)
            ->where('menu.name', 'Food')
            ->has('search_index.menus')
            ->has('search_index.words')
        );
});

test('inertia visits with the except-once header omit the search index', function () {
    $this->seed(BoardTemplateSeeder::class);

    $food = Menu::query()->template()->whereNull('parent_id')->where('name', 'Food')->firstOrFail();

    $document = $this->get('/board/'.$food->slug)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('board/Show')
            ->has('search_index.words')
        );

    /** @var array{version: string|null} $page */
    $page = $document->viewData('page');

    DB::flushQueryLog();
    DB::enableQueryLog();

    $visit = $this->get('/board/'.$food->slug, [
        Header::INERTIA => 'true',
        Header::VERSION => (string) $page['version'],
        Header::EXCEPT_ONCE_PROPS => 'search_index',
    ]);

    $visit->assertOk();

    expect($visit->json('component'))->toBe('board/Show')
        ->and($visit->json('props'))->not->toHaveKey('search_index');

    $searchIndexQueries = collect(DB::getQueryLog())->filter(function (array $query): bool {
        $sql = strtolower(str_replace(['`', '"'], '', $query['query']));

        return str_contains($sql, 'select id, label, menu_id from words')
            || str_contains($sql, 'select id, name, parent_id from menus');
    });

    expect($searchIndexQueries)->toBeEmpty();

    DB::disableQueryLog();
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
