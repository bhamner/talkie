<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Phrase;
use App\Models\Word;
use App\Support\CoreVocabulary;
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

        $this->createHomeWords(CoreVocabulary::homeWords());

        Word::query()
            ->template()
            ->whereNull('menu_id')
            ->where('label', 'I')
            ->update(['speak_text' => 'eye']);

        $this->phrases(null, [
            'I need help',
            'Please help me',
            'Yes please',
            'No thank you',
            'I am finished',
            'I want more',
        ]);

        $joiners = $this->menu('Joiners', null, 1);
        $this->words($joiners, [
            'and', 'or', 'but', 'so', 'because', 'if', 'while', 'than', 'then', 'also', 'too', 'as',
        ]);

        $whereWhen = $this->menu('Where & when', null, 2);
        $this->words($whereWhen, [
            'to', 'from', 'for', 'of', 'at', 'by', 'with', 'about', 'through', 'over', 'up', 'down', 'around',
            'away', 'inside', 'before', 'after', 'sometimes', 'somewhere',
        ]);

        $canWill = $this->menu('Can & will', null, 3);
        $this->words($canWill, [
            'can', 'could', 'would', 'may', 'must', 'let',
        ]);

        $doDid = $this->menu('Do & did', null, 4);
        $this->words($doDid, [
            'do', 'does', 'did', 'be', 'are', 'was', 'were', 'have', 'has', 'had',
        ]);

        $thisThat = $this->menu('This & that', null, 5);
        $this->words($thisThat, [
            'this', 'these', 'those', 'there', 'same', 'other', 'another', 'something', 'everything',
        ]);

        $amount = $this->menu('Amount', null, 6);
        $this->words($amount, [
            'all', 'any', 'both', 'lot', 'many', 'most', 'much', 'an', 'else', 'only',
        ]);

        $really = $this->menu('Really', null, 7);
        $this->words($really, [
            'really', 'very', 'just', 'even', 'oh', 'way', 'together', 'like', 'kind',
        ]);

        $questions = $this->menu('Questions', null, 8);
        $this->words($questions, [
            'how', 'where', 'when', 'which', 'who', 'why',
        ]);

        $food = $this->menu('Food', null, 9);
        $this->words($food, [
            'hungry', 'thirsty', 'eat', 'snack', 'breakfast', 'lunch', 'dinner', 'meal', 'food', 'cook',
            'cookie', 'candy', 'corn', 'bean', 'bite', 'cup', 'drink', 'apple', 'pie', 'banana',
            'chicken', 'cheese', 'cake',
        ]);
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

        $feelings = $this->menu('Feelings', null, 10);
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

        $friends = $this->menu('Friends', null, 11);
        $this->words($friends, [
            'mom', 'dad', 'baby', 'boy', 'girl', 'man', 'woman', 'friend', 'teacher', 'doctor',
            'people', 'name', 'hi', 'hello', 'bye', 'me', 'myself',
            'he', 'him', 'his', 'she', 'her', 'we', 'us', 'our',
            'they', 'them', 'their', 'your', 'somebody', 'someone', 'everybody',
        ]);
        $this->phrases($friends, [
            'I want my mom',
            'I want my dad',
            'Where is my friend?',
            'I need the teacher',
            'I need the doctor',
        ]);

        $places = $this->menu('Places', null, 12);
        $this->words($places, [
            'school', 'outside', 'bathroom', 'store', 'park', 'beach', 'hill', 'side', 'middle', 'top',
        ]);
        $this->phrases($places, [
            'I want to go home',
            'I want to go to school',
            'I want to go outside',
            'I need the bathroom',
            'I want to go to the park',
            'I want to go to the beach',
        ]);

        $actions = $this->menu('Actions', null, 13);
        $this->words($actions, [
            'stop', 'come', 'look', 'wait', 'play', 'sleep', 'bet', 'buy', 'call', 'came',
            'catch', 'cut', 'fall', 'find', 'fix', 'fly', 'found', 'get', 'give',
            'going', 'hear', 'hold', 'jump', 'know', 'lift', 'made', 'make',
            'mean', 'move', 'open', 'paint', 'pick', 'push', 'put', 'remember', 'ride', 'run', 'said', 'saw', 'say',
            'see', 'show', 'shut', 'sit', 'swing', 'tell', 'throw', 'threw', 'try', 'turn', 'use',
            'watch', 'went',
        ]);
        $this->phrases($actions, [
            'I want to play',
            'I want to sleep',
            'Please stop',
            'Come here',
            'Look at this',
            'Please wait',
        ]);

        $describing = $this->menu('Describing', null, 14);
        $this->words($describing, [
            'bad', 'better', 'big', 'small', 'different', 'first', 'good', 'great', 'high', 'hot', 'last', 'little', 'long',
            'messy', 'new', 'nice', 'old', 'ready', 'right', 'left', 'well', 'whole',
        ]);

        $stuff = $this->menu('Stuff', null, 15);
        $this->words($stuff, ['ball', 'toy', 'tape', 'piece', 'thing']);

        $home = $this->menu('Home', null, 16);
        $this->words($home, [
            'home', 'house', 'room', 'door', 'bed', 'chair', 'table', 'couch', 'sink', 'toilet',
        ]);

        $vehicles = $this->menu('Vehicles', null, 17);
        $this->words($vehicles, [
            'car', 'truck', 'motorcycle', 'bicycle', 'bus', 'golf cart',
        ]);

        $nature = $this->menu('Nature', null, 18);
        $this->words($nature, ['tree', 'leaf', 'fire']);

        $time = $this->menu('Time', null, 19);
        $this->words($time, [
            'day', 'night', 'time', 'today', 'again', 'already', 'almost', 'never', 'next', 'still', 'yet', 'maybe',
        ]);

        $animals = $this->menu('Animals', null, 20);
        $this->words($animals, ['ant', 'bird', 'bug', 'dog', 'duck', 'horse', 'turtle']);

        $body = $this->menu('Body', null, 21);
        $this->words($body, [
            'face', 'finger', 'foot', 'hair', 'hand', 'head', 'shoulder', 'back', 'muscle', 'leg',
        ]);

        $colors = $this->menu('Colors', null, 22);
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

        $shapes = $this->menu('Shapes', null, 23);
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

        $numbers = $this->menu('Numbers', null, 24);
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
            'is_builtin' => true,
            'is_hidden' => false,
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
                'is_builtin' => true,
                'is_hidden' => false,
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
                'is_builtin' => true,
                'is_hidden' => false,
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
                'is_builtin' => true,
                'is_hidden' => false,
            ]);
        }
    }
}
