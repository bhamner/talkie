<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Phrase;
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
        Phrase::query()->template()->delete();

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

        $this->phrases(null, [
            'I need help',
            'Please help me',
            'Yes please',
            'No thank you',
            'I am finished',
            'I want more',
        ]);

        $food = $this->menu('Food', null, 1);
        $this->words($food, ['hungry', 'eat', 'snack', 'breakfast', 'lunch', 'dinner']);
        $this->phrases($food, [
            'I am hungry',
            'I am thirsty',
            'I want a snack',
            'I want breakfast',
            'I want lunch',
            'I want dinner',
            'That tastes good',
            'I do not like that',
        ]);

        $drinks = $this->menu('Drinks', $food, 1);
        $this->words($drinks, ['water', 'juice', 'milk', 'coffee', 'tea']);
        $this->phrases($drinks, [
            'I want water',
            'I want juice',
            'I want milk',
            'Can I have a drink?',
        ]);

        $feelings = $this->menu('Feelings', null, 2);
        $this->words($feelings, ['happy', 'sad', 'tired', 'mad', 'scared', 'hurt', 'okay']);
        $this->phrases($feelings, [
            'I am happy',
            'I am sad',
            'I am tired',
            'I am mad',
            'I am scared',
            'I am hurt',
            'I am okay',
            'I do not feel well',
        ]);

        $people = $this->menu('People', null, 3);
        $this->words($people, ['mom', 'dad', 'friend', 'teacher', 'doctor', 'me']);
        $this->phrases($people, [
            'I want my mom',
            'I want my dad',
            'Where is my friend?',
            'I need the teacher',
            'I need the doctor',
        ]);

        $places = $this->menu('Places', null, 4);
        $this->words($places, ['home', 'school', 'outside', 'bathroom', 'store', 'park']);
        $this->phrases($places, [
            'I want to go home',
            'I want to go to school',
            'I want to go outside',
            'I need the bathroom',
            'I want to go to the park',
        ]);

        $actions = $this->menu('Actions', null, 5);
        $this->words($actions, ['go', 'stop', 'come', 'look', 'wait', 'play', 'sleep']);
        $this->phrases($actions, [
            'I want to play',
            'I want to sleep',
            'Please stop',
            'Come here',
            'Look at this',
            'Please wait',
        ]);

        $colors = $this->menu('Colors', null, 6);
        $this->words($colors, [
            'red',
            'orange',
            'yellow',
            'green',
            'blue',
            'purple',
            'pink',
            'black',
            'white',
            'brown',
        ]);
        $this->phrases($colors, [
            'I like this color',
            'What color is that?',
            'My favorite color is blue',
            'Can we use red?',
        ]);

        $shapes = $this->menu('Shapes', null, 7);
        $this->words($shapes, [
            'circle',
            'square',
            'triangle',
            'rectangle',
            'star',
            'heart',
            'oval',
            'diamond',
        ]);
        $this->phrases($shapes, [
            'That is a circle',
            'That is a square',
            'That is a triangle',
            'I see a star',
            'I like that shape',
        ]);

        $numbers = $this->menu('Numbers', null, 8);
        $this->wordsWithSpeech($numbers, [
            ['0', 'zero'],
            ['1', 'one'],
            ['2', 'two'],
            ['3', 'three'],
            ['4', 'four'],
            ['5', 'five'],
            ['6', 'six'],
            ['7', 'seven'],
            ['8', 'eight'],
            ['9', 'nine'],
            ['10', 'ten'],
        ]);
        $this->phrases($numbers, [
            'How many?',
            'I want one',
            'I want two',
            'Count with me',
            'That is my number',
        ]);
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

    /**
     * @param  list<array{0: string, 1: string}>  $entries
     */
    private function wordsWithSpeech(?Menu $menu, array $entries): void
    {
        foreach ($entries as $index => [$label, $speakText]) {
            Word::create([
                'user_id' => null,
                'menu_id' => $menu?->id,
                'label' => $label,
                'speak_text' => $speakText,
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * @param  list<string>  $texts
     */
    private function phrases(?Menu $menu, array $texts): void
    {
        foreach ($texts as $index => $text) {
            Phrase::create([
                'user_id' => null,
                'menu_id' => $menu?->id,
                'text' => $text,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
