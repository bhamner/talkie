<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveBoardItemRequest;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\Menu;
use App\Models\Phrase;
use App\Models\Word;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $parent = $request->parent();
        $user = $request->user();
        $validated = $request->validated();

        $nextSortOrder = (int) Menu::query()
            ->forUser($user)
            ->where('parent_id', $parent?->id)
            ->max('sort_order') + 1;

        Menu::create([
            'user_id' => $user->id,
            'parent_id' => $parent?->id,
            'name' => trim($validated['name']),
            'sort_order' => $nextSortOrder,
            'is_builtin' => false,
            'is_hidden' => false,
        ]);

        return back();
    }

    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        abort_unless($menu->user_id === $request->user()->id, 404);

        $menu->update([
            'name' => trim($request->validated('name')),
        ]);

        return back();
    }

    public function destroy(Request $request, Menu $menu): RedirectResponse
    {
        abort_unless($menu->user_id === $request->user()->id, 404);
        abort_if($menu->is_builtin, 403);

        DB::transaction(function () use ($menu): void {
            $this->deleteMenuSubtree($menu);
        });

        return back();
    }

    public function hide(Request $request, Menu $menu): RedirectResponse
    {
        abort_unless($menu->user_id === $request->user()->id, 404);
        abort_unless($menu->is_builtin, 403);

        $menu->update(['is_hidden' => true]);

        return back();
    }

    public function unhide(Request $request, Menu $menu): RedirectResponse
    {
        abort_unless($menu->user_id === $request->user()->id, 404);
        abort_unless($menu->is_builtin, 403);

        $menu->update(['is_hidden' => false]);

        return back();
    }

    public function move(MoveBoardItemRequest $request, Menu $menu): RedirectResponse
    {
        abort_unless($menu->user_id === $request->user()->id, 404);

        $direction = $request->validated('direction');

        $siblings = Menu::query()
            ->forUser($request->user())
            ->when(
                $menu->parent_id === null,
                fn ($query) => $query->whereNull('parent_id'),
                fn ($query) => $query->where('parent_id', $menu->parent_id),
            );

        $sibling = (clone $siblings)
            ->when(
                $direction === 'up',
                fn ($query) => $query->where('sort_order', '<', $menu->sort_order)->orderByDesc('sort_order'),
                fn ($query) => $query->where('sort_order', '>', $menu->sort_order)->orderBy('sort_order'),
            )
            ->first();

        if ($sibling) {
            $currentOrder = $menu->sort_order;
            $menu->update(['sort_order' => $sibling->sort_order]);
            $sibling->update(['sort_order' => $currentOrder]);
        }

        return back();
    }

    private function deleteMenuSubtree(Menu $menu): void
    {
        $children = Menu::query()
            ->where('user_id', $menu->user_id)
            ->where('parent_id', $menu->id)
            ->get();

        foreach ($children as $child) {
            $this->deleteMenuSubtree($child);
        }

        Word::query()
            ->where('user_id', $menu->user_id)
            ->where('menu_id', $menu->id)
            ->delete();

        Phrase::query()
            ->where('user_id', $menu->user_id)
            ->where('menu_id', $menu->id)
            ->delete();

        $menu->delete();
    }
}
