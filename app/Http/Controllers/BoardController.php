<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Word;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BoardController extends Controller
{
    public function show(Request $request, ?Menu $menu = null): Response
    {
        $user = $request->user();

        if ($menu) {
            if ($user) {
                abort_unless($menu->user_id === $user->id, 404);
            } else {
                abort_unless($menu->user_id === null, 404);
            }
        }

        $menusQuery = Menu::query()->where('parent_id', $menu?->id)->orderBy('sort_order');
        $wordsQuery = Word::query()->where('menu_id', $menu?->id)->orderBy('sort_order');

        if ($user) {
            $menusQuery->forUser($user);
            $wordsQuery->forUser($user);
        } else {
            $menusQuery->template();
            $wordsQuery->template();
        }

        $menus = $menusQuery->get(['id', 'name', 'parent_id', 'sort_order']);
        $words = $wordsQuery->get(['id', 'label', 'speak_text', 'menu_id', 'sort_order']);

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

        return Inertia::render('board/Show', [
            'menu' => $menu ? [
                'id' => $menu->id,
                'name' => $menu->name,
                'parent_id' => $menu->parent_id,
            ] : null,
            'menus' => $menus,
            'words' => $words->map(fn (Word $word) => [
                'id' => $word->id,
                'label' => $word->label,
                'speak_text' => $word->textToSpeak(),
            ]),
            'ancestors' => $ancestors,
            'is_guest' => $user === null,
            'preferred_name' => $user?->preferred_name,
            'voice' => [
                'id' => $user?->settings?->voice_id,
                'uri' => $user?->settings?->voice_uri,
                'name' => $user?->settings?->voice_name,
            ],
        ]);
    }
}
