<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Phrase;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Word;
use App\Support\BoardIcons;
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
            $this->backfillMissingIconsFromCatalog();

            if (! $user->menus()->exists() && ! $user->words()->exists()) {
                $this->copyAllTemplateContent($user);

                return;
            }

            $this->renameObsoleteMenus($user);
            $this->syncMissingTopLevelMenus($user);
            $this->syncMissingPhrases($user);
            $this->syncIconsFromTemplate($user);
        });
    }

    /**
     * Add any new top-level template categories and phrases the user is missing.
     */
    public function syncMissingCategoriesToUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->ensureSettings($user);
            $this->backfillMissingIconsFromCatalog();
            $this->renameObsoleteMenus($user);
            $this->syncMissingTopLevelMenus($user);
            $this->syncMissingPhrases($user);
            $this->syncIconsFromTemplate($user);
        });
    }

    public function backfillMissingIconsFromCatalog(): void
    {
        $wordIdsByIcon = [];

        Word::query()
            ->whereNull('icon')
            ->orderBy('id')
            ->each(function (Word $word) use (&$wordIdsByIcon): void {
                $icon = BoardIcons::forWord($word->label);

                if ($icon === null) {
                    return;
                }

                $wordIdsByIcon[$icon][] = $word->id;
            });

        foreach ($wordIdsByIcon as $icon => $ids) {
            Word::query()->whereIn('id', $ids)->update(['icon' => $icon]);
        }

        $menuIdsByIcon = [];

        Menu::query()
            ->whereNull('icon')
            ->orderBy('id')
            ->each(function (Menu $menu) use (&$menuIdsByIcon): void {
                $icon = BoardIcons::forFolder($menu->name);

                if ($icon === null) {
                    return;
                }

                $menuIdsByIcon[$icon][] = $menu->id;
            });

        foreach ($menuIdsByIcon as $icon => $ids) {
            Menu::query()->whereIn('id', $ids)->update(['icon' => $icon]);
        }
    }

    private function renameObsoleteMenus(User $user): void
    {
        Menu::query()
            ->forUser($user)
            ->whereNull('parent_id')
            ->where('name', 'Small words')
            ->update(['name' => 'Where']);

        Menu::query()
            ->forUser($user)
            ->whereNull('parent_id')
            ->where('name', 'Where')
            ->update(['name' => 'Where & when']);

        Menu::query()
            ->forUser($user)
            ->whereNull('parent_id')
            ->where('name', 'Toys')
            ->update(['name' => 'Stuff']);

        Menu::query()
            ->forUser($user)
            ->whereNull('parent_id')
            ->where('name', 'Furniture')
            ->update(['name' => 'Home']);

        $friendsExists = Menu::query()
            ->forUser($user)
            ->whereNull('parent_id')
            ->where('name', 'Friends')
            ->exists();

        if (! $friendsExists) {
            Menu::query()
                ->forUser($user)
                ->whereNull('parent_id')
                ->where('name', 'People')
                ->update(['name' => 'Friends']);
        }

        $friendsMenu = Menu::query()
            ->forUser($user)
            ->whereNull('parent_id')
            ->where('name', 'Friends')
            ->first();

        $socialMenu = Menu::query()
            ->forUser($user)
            ->whereNull('parent_id')
            ->where('name', 'Social')
            ->first();

        if ($friendsMenu !== null && $socialMenu !== null) {
            Word::query()
                ->forUser($user)
                ->where('menu_id', $socialMenu->id)
                ->update(['menu_id' => $friendsMenu->id]);

            Phrase::query()
                ->forUser($user)
                ->where('menu_id', $socialMenu->id)
                ->update(['menu_id' => $friendsMenu->id]);

            $socialMenu->delete();
        }

        $this->migratePronounsFolder($user, $friendsMenu);
        $this->migrateHomePlaceWords($user);
        $this->renameBuiltinWordLabels($user);
        $this->deleteObsoleteBuiltinWords($user);
        $this->syncMissingHomeWords($user);

        $this->syncMissingMenuWords($user, 'Friends');
        $this->syncMissingMenuWords($user, 'Home');
        $this->syncMissingMenuWords($user, 'Stuff');
        $this->syncMissingMenuWords($user, 'Where & when');
        $this->syncMissingMenuWords($user, 'Body');
        $this->syncMissingMenuWords($user, 'Vehicles');
        $this->syncMissingMenuWords($user, 'Time');
        $this->syncMissingMenuWords($user, 'Food');
        $this->syncMissingMenuWords($user, 'Describing');
        $this->syncMissingMenuWords($user, 'Actions');
        $this->syncMissingMenuWords($user, 'Places');
    }

    private function migratePronounsFolder(User $user, ?Menu $friendsMenu): void
    {
        $pronounsMenu = Menu::query()
            ->forUser($user)
            ->whereNull('parent_id')
            ->where('name', 'Pronouns')
            ->first();

        if ($pronounsMenu === null) {
            return;
        }

        $thisThatMenu = Menu::query()
            ->forUser($user)
            ->whereNull('parent_id')
            ->where('name', 'This & that')
            ->first();

        $whereWhenMenu = Menu::query()
            ->forUser($user)
            ->whereNull('parent_id')
            ->where('name', 'Where & when')
            ->first();

        $toFriends = [
            'me', 'myself', 'he', 'him', 'his', 'she', 'her', 'we', 'us', 'our',
            'they', 'them', 'their', 'your', 'somebody', 'someone', 'everybody',
        ];
        $toThisThat = ['something', 'everything'];
        $toWhereWhen = ['sometimes', 'somewhere'];

        if ($friendsMenu !== null) {
            Word::query()
                ->forUser($user)
                ->where('menu_id', $pronounsMenu->id)
                ->whereIn('label', $toFriends)
                ->update(['menu_id' => $friendsMenu->id]);
        }

        if ($thisThatMenu !== null) {
            Word::query()
                ->forUser($user)
                ->where('menu_id', $pronounsMenu->id)
                ->whereIn('label', $toThisThat)
                ->update(['menu_id' => $thisThatMenu->id]);
        }

        if ($whereWhenMenu !== null) {
            Word::query()
                ->forUser($user)
                ->where('menu_id', $pronounsMenu->id)
                ->whereIn('label', $toWhereWhen)
                ->update(['menu_id' => $whereWhenMenu->id]);
        }

        Word::query()->forUser($user)->where('menu_id', $pronounsMenu->id)->delete();
        Phrase::query()->forUser($user)->where('menu_id', $pronounsMenu->id)->delete();
        $pronounsMenu->delete();
    }

    private function renameBuiltinWordLabels(User $user): void
    {
        $renames = [
            'gonna' => 'going',
            'fixed' => 'fix',
            'toys' => 'toy',
            "what's" => 'what',
        ];

        foreach ($renames as $from => $to) {
            Word::query()
                ->forUser($user)
                ->where('is_builtin', true)
                ->where('label', $from)
                ->update(['label' => $to]);
        }
    }

    private function deleteObsoleteBuiltinWords(User $user): void
    {
        Word::query()
            ->forUser($user)
            ->where('is_builtin', true)
            ->whereIn('label', [
                'mommy', 'guys', 'box', 'stuff',
                "can't", "couldn't", "won't", "let's",
                "don't", "doesn't", "didn't", "aren't", "wasn't", "haven't", "isn't", 'being',
                "there's", "here's", "that's", "it's",
                "where's",
                "I'm", "I'll", "you'll", "you're", "he's", "she's", "we'll", "we're", "they'll", "they're",
                'ours', 'yours',
            ])
            ->delete();

        Word::query()
            ->forUser($user)
            ->where('is_builtin', true)
            ->where('label', 'not')
            ->whereHas('menu', fn ($menu) => $menu->where('name', 'Really'))
            ->delete();
    }

    private function migrateHomePlaceWords(User $user): void
    {
        $homeMenu = Menu::query()
            ->forUser($user)
            ->whereNull('parent_id')
            ->where('name', 'Home')
            ->first();

        $placesMenu = Menu::query()
            ->forUser($user)
            ->whereNull('parent_id')
            ->where('name', 'Places')
            ->first();

        if ($homeMenu === null || $placesMenu === null) {
            return;
        }

        Word::query()
            ->forUser($user)
            ->where('menu_id', $placesMenu->id)
            ->whereIn('label', ['home', 'house', 'room', 'door'])
            ->update(['menu_id' => $homeMenu->id]);
    }

    private function syncMissingHomeWords(User $user): void
    {
        $existingLabels = Word::query()
            ->forUser($user)
            ->whereNull('menu_id')
            ->pluck('label')
            ->map(fn (string $label) => strtolower($label))
            ->all();

        $templateWords = Word::query()
            ->template()
            ->whereNull('menu_id')
            ->orderBy('sort_order')
            ->get();

        foreach ($templateWords as $templateWord) {
            if (in_array(strtolower($templateWord->label), $existingLabels, true)) {
                continue;
            }

            Word::create([
                'user_id' => $user->id,
                'menu_id' => null,
                'label' => $templateWord->label,
                'icon' => $templateWord->icon,
                'speak_text' => $templateWord->speak_text,
                'sort_order' => $templateWord->sort_order,
                'is_builtin' => true,
                'is_hidden' => false,
            ]);
        }
    }

    private function syncMissingMenuWords(User $user, string $menuName): void
    {
        $userMenu = Menu::query()
            ->forUser($user)
            ->whereNull('parent_id')
            ->where('name', $menuName)
            ->first();

        $templateMenu = Menu::query()
            ->template()
            ->whereNull('parent_id')
            ->where('name', $menuName)
            ->first();

        if ($userMenu === null || $templateMenu === null) {
            return;
        }

        $existingLabels = Word::query()
            ->forUser($user)
            ->where('menu_id', $userMenu->id)
            ->pluck('label')
            ->map(fn (string $label) => strtolower($label))
            ->all();

        $templateWords = Word::query()
            ->template()
            ->where('menu_id', $templateMenu->id)
            ->orderBy('sort_order')
            ->get();

        foreach ($templateWords as $templateWord) {
            if (in_array(strtolower($templateWord->label), $existingLabels, true)) {
                continue;
            }

            Word::create([
                'user_id' => $user->id,
                'menu_id' => $userMenu->id,
                'label' => $templateWord->label,
                'icon' => $templateWord->icon,
                'speak_text' => $templateWord->speak_text,
                'sort_order' => $templateWord->sort_order,
                'is_builtin' => true,
                'is_hidden' => false,
            ]);
        }
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
                'icon' => $templateMenu->icon,
                'sort_order' => $templateMenu->sort_order,
                'is_builtin' => true,
                'is_hidden' => false,
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
                'icon' => $templateWord->icon,
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
            'icon' => $templateMenu->icon,
            'sort_order' => $templateMenu->sort_order,
            'is_builtin' => true,
            'is_hidden' => false,
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
                'icon' => $templateWord->icon,
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

    private function syncIconsFromTemplate(User $user): void
    {
        $templateWordIcons = Word::query()
            ->template()
            ->whereNotNull('icon')
            ->get(['label', 'icon']);

        foreach ($templateWordIcons as $templateWord) {
            Word::query()
                ->forUser($user)
                ->whereRaw('LOWER(label) = ?', [strtolower($templateWord->label)])
                ->where(function ($query) use ($templateWord) {
                    $query->whereNull('icon')->orWhere('icon', '!=', $templateWord->icon);
                })
                ->update(['icon' => $templateWord->icon]);
        }

        $templateMenuIcons = Menu::query()
            ->template()
            ->whereNotNull('icon')
            ->get(['name', 'icon']);

        foreach ($templateMenuIcons as $templateMenu) {
            Menu::query()
                ->forUser($user)
                ->where('name', $templateMenu->name)
                ->where(function ($query) use ($templateMenu) {
                    $query->whereNull('icon')->orWhere('icon', '!=', $templateMenu->icon);
                })
                ->update(['icon' => $templateMenu->icon]);
        }
    }
}
