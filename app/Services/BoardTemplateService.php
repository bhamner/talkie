<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Phrase;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Word;
use Illuminate\Support\Facades\DB;

class BoardTemplateService
{
    /**
     * Copy the shared template board (user_id null) into a user account.
     * Safe to call multiple times — full copy once, then syncs missing content.
     */
    public function copyToUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->ensureSettings($user);

            if (! $user->menus()->exists() && ! $user->words()->exists()) {
                $this->copyAllTemplateContent($user);

                return;
            }

            $this->syncMissingTopLevelMenus($user);
            $this->syncMissingPhrases($user);
        });
    }

    /**
     * Add any new top-level template categories and phrases the user is missing.
     */
    public function syncMissingCategoriesToUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->ensureSettings($user);
            $this->renameObsoleteMenus($user);
            $this->syncMissingTopLevelMenus($user);
            $this->syncMissingPhrases($user);
        });
    }

    private function renameObsoleteMenus(User $user): void
    {
        Menu::query()
            ->forUser($user)
            ->whereNull('parent_id')
            ->where('name', 'Small words')
            ->update(['name' => 'Where']);
    }

    private function ensureSettings(User $user): void
    {
        UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'voice_id' => null,
                'voice_uri' => null,
                'voice_name' => null,
                'extras' => [],
            ]
        );
    }

    private function copyAllTemplateContent(User $user): void
    {
        $menuIdMap = [];

        $templateMenus = Menu::query()
            ->template()
            ->orderBy('id')
            ->get();

        foreach ($templateMenus as $templateMenu) {
            $copy = Menu::create([
                'user_id' => $user->id,
                'parent_id' => null,
                'name' => $templateMenu->name,
                'sort_order' => $templateMenu->sort_order,
            ]);

            $menuIdMap[$templateMenu->id] = $copy->id;
        }

        foreach ($templateMenus as $templateMenu) {
            if ($templateMenu->parent_id === null) {
                continue;
            }

            Menu::whereKey($menuIdMap[$templateMenu->id])->update([
                'parent_id' => $menuIdMap[$templateMenu->parent_id] ?? null,
            ]);
        }

        $templateWords = Word::query()
            ->template()
            ->orderBy('id')
            ->get();

        foreach ($templateWords as $templateWord) {
            Word::create([
                'user_id' => $user->id,
                'menu_id' => $templateWord->menu_id
                    ? ($menuIdMap[$templateWord->menu_id] ?? null)
                    : null,
                'label' => $templateWord->label,
                'speak_text' => $templateWord->speak_text,
                'sort_order' => $templateWord->sort_order,
                'is_builtin' => true,
                'is_hidden' => false,
            ]);
        }

        $templatePhrases = Phrase::query()
            ->template()
            ->orderBy('id')
            ->get();

        foreach ($templatePhrases as $templatePhrase) {
            Phrase::create([
                'user_id' => $user->id,
                'menu_id' => $templatePhrase->menu_id
                    ? ($menuIdMap[$templatePhrase->menu_id] ?? null)
                    : null,
                'text' => $templatePhrase->text,
                'sort_order' => $templatePhrase->sort_order,
                'is_builtin' => true,
                'is_hidden' => false,
            ]);
        }
    }

    private function syncMissingTopLevelMenus(User $user): void
    {
        $existingNames = $user->menus()
            ->whereNull('parent_id')
            ->pluck('name')
            ->all();

        $templateTopLevelMenus = Menu::query()
            ->template()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        foreach ($templateTopLevelMenus as $templateMenu) {
            if (in_array($templateMenu->name, $existingNames, true)) {
                continue;
            }

            $this->copyMenuSubtree($user, $templateMenu, null);
        }
    }

    private function syncMissingPhrases(User $user): void
    {
        $this->copyMissingPhrasesForMenu($user, null, null);

        $userMenus = $user->menus()->with('parent')->orderBy('id')->get();

        foreach ($userMenus as $userMenu) {
            $templateMenu = $this->findMatchingTemplateMenu($userMenu);

            if ($templateMenu === null) {
                continue;
            }

            $this->copyMissingPhrasesForMenu($user, $userMenu->id, $templateMenu->id);
        }
    }

    private function findMatchingTemplateMenu(Menu $userMenu): ?Menu
    {
        $query = Menu::query()
            ->template()
            ->where('name', $userMenu->name);

        if ($userMenu->parent_id === null) {
            return $query->whereNull('parent_id')->first();
        }

        $parentName = $userMenu->parent?->name;

        if ($parentName === null) {
            return null;
        }

        return $query
            ->whereHas('parent', fn ($parentQuery) => $parentQuery->template()->where('name', $parentName))
            ->first();
    }

    private function copyMissingPhrasesForMenu(User $user, ?int $userMenuId, ?int $templateMenuId): void
    {
        $existingTexts = Phrase::query()
            ->forUser($user)
            ->where('menu_id', $userMenuId)
            ->pluck('text')
            ->all();

        $templatePhrases = Phrase::query()
            ->template()
            ->where('menu_id', $templateMenuId)
            ->orderBy('sort_order')
            ->get();

        foreach ($templatePhrases as $templatePhrase) {
            if (in_array($templatePhrase->text, $existingTexts, true)) {
                continue;
            }

            Phrase::create([
                'user_id' => $user->id,
                'menu_id' => $userMenuId,
                'text' => $templatePhrase->text,
                'sort_order' => $templatePhrase->sort_order,
                'is_builtin' => true,
                'is_hidden' => false,
            ]);
        }
    }

    private function copyMenuSubtree(User $user, Menu $templateMenu, ?int $parentId): void
    {
        $copy = Menu::create([
            'user_id' => $user->id,
            'parent_id' => $parentId,
            'name' => $templateMenu->name,
            'sort_order' => $templateMenu->sort_order,
        ]);

        $templateWords = Word::query()
            ->template()
            ->where('menu_id', $templateMenu->id)
            ->orderBy('sort_order')
            ->get();

        foreach ($templateWords as $templateWord) {
            Word::create([
                'user_id' => $user->id,
                'menu_id' => $copy->id,
                'label' => $templateWord->label,
                'speak_text' => $templateWord->speak_text,
                'sort_order' => $templateWord->sort_order,
                'is_builtin' => true,
                'is_hidden' => false,
            ]);
        }

        $templatePhrases = Phrase::query()
            ->template()
            ->where('menu_id', $templateMenu->id)
            ->orderBy('sort_order')
            ->get();

        foreach ($templatePhrases as $templatePhrase) {
            Phrase::create([
                'user_id' => $user->id,
                'menu_id' => $copy->id,
                'text' => $templatePhrase->text,
                'sort_order' => $templatePhrase->sort_order,
                'is_builtin' => true,
                'is_hidden' => false,
            ]);
        }

        $childMenus = Menu::query()
            ->template()
            ->where('parent_id', $templateMenu->id)
            ->orderBy('sort_order')
            ->get();

        foreach ($childMenus as $childMenu) {
            $this->copyMenuSubtree($user, $childMenu, $copy->id);
        }
    }
}
