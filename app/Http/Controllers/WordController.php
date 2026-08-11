<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveBoardItemRequest;
use App\Http\Requests\StoreWordRequest;
use App\Http\Requests\UpdateWordRequest;
use App\Models\Word;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WordController extends Controller
{
    public function store(StoreWordRequest $request): RedirectResponse
    {
        $menu = $request->menu();
        $user = $request->user();
        $validated = $request->validated();

        $nextSortOrder = (int) Word::query()
            ->forUser($user)
            ->where('menu_id', $menu?->id)
            ->max('sort_order') + 1;

        Word::create([
            'user_id' => $user->id,
            'menu_id' => $menu?->id,
            'label' => trim($validated['label']),
            'speak_text' => filled($validated['speak_text'] ?? null) ? trim($validated['speak_text']) : null,
            'sort_order' => $nextSortOrder,
            'is_builtin' => false,
            'is_hidden' => false,
        ]);

        return back();
    }

    public function update(UpdateWordRequest $request, Word $word): RedirectResponse
    {
        abort_unless($word->user_id === $request->user()->id, 404);

        $validated = $request->validated();

        $word->update([
            'label' => trim($validated['label']),
            'speak_text' => filled($validated['speak_text'] ?? null) ? trim($validated['speak_text']) : null,
        ]);

        return back();
    }

    public function destroy(Request $request, Word $word): RedirectResponse
    {
        abort_unless($word->user_id === $request->user()->id, 404);
        abort_if($word->is_builtin, 403);

        $word->delete();

        return back();
    }

    public function hide(Request $request, Word $word): RedirectResponse
    {
        abort_unless($word->user_id === $request->user()->id, 404);
        abort_unless($word->is_builtin, 403);

        $word->update(['is_hidden' => true]);

        return back();
    }

    public function unhide(Request $request, Word $word): RedirectResponse
    {
        abort_unless($word->user_id === $request->user()->id, 404);
        abort_unless($word->is_builtin, 403);

        $word->update(['is_hidden' => false]);

        return back();
    }

    public function move(MoveBoardItemRequest $request, Word $word): RedirectResponse
    {
        abort_unless($word->user_id === $request->user()->id, 404);

        $direction = $request->validated('direction');

        $siblings = Word::query()
            ->forUser($request->user())
            ->when(
                $word->menu_id === null,
                fn ($query) => $query->whereNull('menu_id'),
                fn ($query) => $query->where('menu_id', $word->menu_id),
            );

        $sibling = (clone $siblings)
            ->when(
                $direction === 'up',
                fn ($query) => $query->where('sort_order', '<', $word->sort_order)->orderByDesc('sort_order'),
                fn ($query) => $query->where('sort_order', '>', $word->sort_order)->orderBy('sort_order'),
            )
            ->first();

        if ($sibling) {
            $currentOrder = $word->sort_order;
            $word->update(['sort_order' => $sibling->sort_order]);
            $sibling->update(['sort_order' => $currentOrder]);
        }

        return back();
    }
}
