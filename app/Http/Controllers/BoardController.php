<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Phrase;
use App\Models\Word;
use App\Services\BoardTemplateService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BoardController extends Controller
{
    public function show(Request $request, ?Menu $menu = null): Response
    {
        $user = $request->user();

        if ($user) {
            app(BoardTemplateService::class)->syncMissingCategoriesToUser($user);
        }

        if ($menu) {
            if ($user) {
                abort_unless($menu->user_id === $user->id, 404);
            } else {
                abort_unless($menu->user_id === null, 404);
            }

            abort_if($menu->is_hidden, 404);
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

        $menus = $menusQuery->get(['id', 'name', 'parent_id', 'sort_order', 'icon', 'is_builtin', 'is_hidden']);
        $words = $wordsQuery->get(['id', 'label', 'icon', 'speak_text', 'menu_id', 'sort_order', 'is_builtin', 'is_hidden']);
        $phrases = $phrasesQuery->get(['id', 'text', 'menu_id', 'sort_order', 'is_builtin', 'is_hidden']);

        $ancestors = [];
        $current = $menu;

        while ($current) {
            array_unshift($ancestors, [
                'id' => $current->id,
                'name' => $current->name,
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

        $searchMenusQuery = Menu::query()->orderBy('sort_order');
        $searchWordsQuery = Word::query()->orderBy('sort_order');

        if ($user) {
            $searchMenusQuery->forUser($user)->visible();
            $searchWordsQuery->forUser($user)->visible();
        } else {
            $searchMenusQuery->template()->visible();
            $searchWordsQuery->template()->visible();
        }

        $searchMenus = $searchMenusQuery->get(['id', 'name', 'parent_id']);
        $menuNames = $searchMenus->pluck('name', 'id');

        return Inertia::render('board/Show', [
            'menu' => $menu ? [
                'id' => $menu->id,
                'name' => $menu->name,
                'parent_id' => $menu->parent_id,
                'icon' => $menu->icon,
            ] : null,
            'menus' => $menus->map(fn (Menu $child) => [
                'id' => $child->id,
                'name' => $child->name,
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
            'search_index' => [
                'menus' => $searchMenus->map(fn (Menu $item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'parent_id' => $item->parent_id,
                ])->values()->all(),
                'words' => $searchWordsQuery->get(['id', 'label', 'menu_id'])->map(fn (Word $word) => [
                    'id' => $word->id,
                    'label' => $word->label,
                    'menu_id' => $word->menu_id,
                    'menu_name' => $word->menu_id ? ($menuNames[$word->menu_id] ?? 'Home') : 'Home',
                ])->values()->all(),
            ],
        ]);
    }
}
