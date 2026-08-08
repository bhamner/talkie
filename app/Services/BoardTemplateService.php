<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Word;
use Illuminate\Support\Facades\DB;

class BoardTemplateService
{
    /**
     * Copy the shared template board (user_id null) into a user account.
     * Safe to call multiple times — skips copy if the user already has content.
     */
    public function copyToUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            UserSetting::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'voice_id' => null,
                    'voice_uri' => null,
                    'voice_name' => null,
                    'extras' => [],
                ]
            );

            if ($user->menus()->exists() || $user->words()->exists()) {
                return;
            }

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
                ]);
            }
        });
    }
}
