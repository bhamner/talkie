<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePhraseRequest;
use App\Models\Phrase;
use Illuminate\Http\RedirectResponse;

class PhraseController extends Controller
{
    public function store(StorePhraseRequest $request): RedirectResponse
    {
        $menu = $request->menu();
        $user = $request->user();

        $nextSortOrder = (int) Phrase::query()
            ->forUser($user)
            ->where('menu_id', $menu?->id)
            ->max('sort_order') + 1;

        Phrase::create([
            'user_id' => $user->id,
            'menu_id' => $menu?->id,
            'text' => $request->validated('text'),
            'sort_order' => $nextSortOrder,
        ]);

        return back();
    }
}
