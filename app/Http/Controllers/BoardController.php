<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Phrase;
use App\Models\User;
use App\Models\Word;
use App\Services\BoardTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BoardController extends Controller
{
    public function show(Request $request, ?string $menu = null): RedirectResponse|Response
    {
        $user = $request->user();
        $segment = $menu;
        $menu = $segment === null ? null : $this->findBoardMenu($user, $segment);

        if ($segment !== null && $menu === null) {
            abort(404);
        }

        if ($user && $menu === null) {
            app(BoardTemplateService::class)->syncMissingCategoriesToUser($user);
        }

        if ($menu) {
            abort_if($menu->is_hidden, 404);

            if ($segment !== null && ctype_digit($segment) && $menu->slug !== $segment) {
                return redirect()->route('board', array_filter([
                    'menu' => $menu->slug,
                    'highlight' => $request->query('highlight'),
                ]));
            }
        }

        $menusQuery = Menu::query()->where('parent_id', $menu?->id)->orderBy('sort_order');
        $wordsQuery = Word::query()->where('menu_id', $menu?->id)->orderBy('sort_order');
        $phrasesQuery = Phrase::query()->where('menu_id', $menu?->id)->orderBy('sort_order');

        if ($user) {
            $menusQuery->forUser($user);
            $wordsQuery->forUser($user);
            $phrasesQuery->forUser($user);
        } else {
            $menusQuery->template();
            $wordsQuery->template();
            $phrasesQuery->template();
        }

        $menus = $menusQuery->get(['id', 'name', 'slug', 'parent_id', 'sort_order', 'icon', 'is_builtin', 'is_hidden']);
        $words = $wordsQuery->get(['id', 'label', 'icon', 'speak_text', 'menu_id', 'sort_order', 'is_builtin', 'is_hidden']);
        $phrases = $phrasesQuery->get(['id', 'text', 'menu_id', 'sort_order', 'is_builtin', 'is_hidden']);

        $ancestors = [];
        $current = $menu;

        while ($current) {
            array_unshift($ancestors, [
                'id' => $current->id,
                'name' => $current->name,
                'slug' => $current->slug,
            ]);

            if (! $current->parent_id) {
                break;
            }

            $parentQuery = Menu::query()->whereKey($current->parent_id);
            $current = $user
                ? $parentQuery->forUser($user)->first()
                : $parentQuery->template()->first();
        }

        $phrasePayload = $phrases->map(fn (Phrase $phrase) => [
            'id' => $phrase->id,
            'text' => $phrase->text,
            'is_greeting' => false,
            'is_builtin' => $phrase->is_builtin,
            'is_hidden' => $phrase->is_hidden,
        ])->values()->all();

        if ($menu === null && $user?->preferred_name) {
            array_unshift($phrasePayload, [
                'id' => 'greeting',
                'text' => 'Hello, my name is '.$user->preferred_name,
                'is_greeting' => true,
                'is_builtin' => true,
                'is_hidden' => false,
            ]);
        }

        return Inertia::render('board/Show', [
            'menu' => $menu ? [
                'id' => $menu->id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'parent_id' => $menu->parent_id,
                'icon' => $menu->icon,
            ] : null,
            'menus' => $menus->map(fn (Menu $child) => [
                'id' => $child->id,
                'name' => $child->name,
                'slug' => $child->slug,
                'parent_id' => $child->parent_id,
                'icon' => $child->icon,
                'is_builtin' => $child->is_builtin,
                'is_hidden' => $child->is_hidden,
            ]),
            'words' => $words->map(fn (Word $word) => [
                'id' => $word->id,
                'label' => $word->label,
                'icon' => $word->icon,
                'speak_text' => $word->speak_text,
                'is_builtin' => $word->is_builtin,
                'is_hidden' => $word->is_hidden,
            ]),
            'phrases' => $phrasePayload,
            'ancestors' => $ancestors,
            'is_guest' => $user === null,
            'can_edit' => $user !== null,
            'preferred_name' => $user?->preferred_name,
            'voice' => [
                'id' => $user?->settings?->voice_id,
                'uri' => $user?->settings?->voice_uri,
                'name' => $user?->settings?->voice_name,
            ],
            'highlight' => $request->query('highlight'),
            'search_index' => Inertia::once(fn () => $this->searchIndex($user)),
        ]);
    }

    /**
     * @return array{menus: list<array{id: int, name: string, slug: string, parent_id: int|null}>, words: list<array{id: int, label: string, menu_id: int|null, menu_name: string, menu_slug: string|null}>}
     */
    private function searchIndex(?User $user): array
    {
        $searchMenusQuery = Menu::query()->orderBy('sort_order');
        $searchWordsQuery = Word::query()->orderBy('sort_order');

        if ($user) {
            $searchMenusQuery->forUser($user)->visible();
            $searchWordsQuery->forUser($user)->visible();
        } else {
            $searchMenusQuery->template()->visible();
            $searchWordsQuery->template()->visible();
        }

        $searchMenus = $searchMenusQuery->get(['id', 'name', 'slug', 'parent_id']);
        $menuNames = $searchMenus->pluck('name', 'id');
        $menuSlugs = $searchMenus->pluck('slug', 'id');

        return [
            'menus' => $searchMenus->map(fn (Menu $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'parent_id' => $item->parent_id,
            ])->values()->all(),
            'words' => $searchWordsQuery->get(['id', 'label', 'menu_id'])->map(fn (Word $word) => [
                'id' => $word->id,
                'label' => $word->label,
                'menu_id' => $word->menu_id,
                'menu_name' => $word->menu_id ? ($menuNames[$word->menu_id] ?? 'Home') : 'Home',
                'menu_slug' => $word->menu_id ? ($menuSlugs[$word->menu_id] ?? null) : null,
            ])->values()->all(),
        ];
    }

    private function findBoardMenu(?User $user, string $segment): ?Menu
    {
        $query = Menu::query();
        $query = $user ? $query->forUser($user) : $query->template();

        $bySlug = (clone $query)->where('slug', $segment)->first();

        if ($bySlug) {
            return $bySlug;
        }

        if (! ctype_digit($segment)) {
            return null;
        }

        return $query->whereKey((int) $segment)->first();
    }
}
