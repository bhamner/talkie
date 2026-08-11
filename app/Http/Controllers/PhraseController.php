<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePhraseRequest;
use App\Models\Phrase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
            'is_builtin' => false,
            'is_hidden' => false,
        ]);

        return back();
    }

    public function destroy(Request $request, Phrase $phrase): RedirectResponse
    {
        abort_unless($phrase->user_id === $request->user()->id, 404);
        abort_if($phrase->is_builtin, 403);

        $phrase->delete();

        return back();
    }

    public function hide(Request $request, Phrase $phrase): RedirectResponse
    {
        abort_unless($phrase->user_id === $request->user()->id, 404);
        abort_unless($phrase->is_builtin, 403);

        $phrase->update(['is_hidden' => true]);

        return back();
    }

    public function unhide(Request $request, Phrase $phrase): RedirectResponse
    {
        abort_unless($phrase->user_id === $request->user()->id, 404);
        abort_unless($phrase->is_builtin, 403);

        $phrase->update(['is_hidden' => false]);

        return back();
    }
}
