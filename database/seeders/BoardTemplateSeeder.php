<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Word;
use Illuminate\Database\Seeder;

class BoardTemplateSeeder extends Seeder
{
    /**
     * Seed the shared starter board (user_id null).
     * Copied to each user on registration.
     */
    public function run(): void
    {
        Menu::query()->template()->delete();
        Word::query()->template()->delete();

        $this->createHomeWords([
            'I',
            'want',
            'need',
            'please',
            'help',
            'yes',
            'no',
            'more',
            'finished',
        ]);

        $food = $this->menu('Food', null, 1);
        $this->words($food, ['hungry', 'eat', 'snack', 'breakfast', 'lunch', 'dinner']);

        $drinks = $this->menu('Drinks', $food, 1);
        $this->words($drinks, ['water', 'juice', 'milk', 'coffee', 'tea']);

        $feelings = $this->menu('Feelings', null, 2);
        $this->words($feelings, ['happy', 'sad', 'tired', 'mad', 'scared', 'hurt', 'okay']);

        $people = $this->menu('People', null, 3);
        $this->words($people, ['mom', 'dad', 'friend', 'teacher', 'doctor', 'me']);

        $places = $this->menu('Places', null, 4);
        $this->words($places, ['home', 'school', 'outside', 'bathroom', 'store', 'park']);

        $actions = $this->menu('Actions', null, 5);
        $this->words($actions, ['go', 'stop', 'come', 'look', 'wait', 'play', 'sleep']);
    }

    private function menu(string $name, ?Menu $parent, int $sortOrder): Menu
    {
        return Menu::create([
            'user_id' => null,
            'parent_id' => $parent?->id,
            'name' => $name,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * @param  list<string>  $labels
     */
    private function words(?Menu $menu, array $labels): void
    {
        foreach ($labels as $index => $label) {
            Word::create([
                'user_id' => null,
                'menu_id' => $menu?->id,
                'label' => $label,
                'speak_text' => null,
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * @param  list<string>  $labels
     */
    private function createHomeWords(array $labels): void
    {
        $this->words(null, $labels);
    }
}
