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

        $where = $this->menu('Where', null, 2);
        $this->words($where, [
            'to', 'from', 'for', 'of', 'at', 'by', 'with', 'about', 'through', 'over', 'up', 'down', 'around',
            'away', 'inside', 'before', 'after',
        ]);

        $canWill = $this->menu('Can & will', null, 3);
        $this->words($canWill, [
            'can', "can't", 'could', "couldn't", "won't", 'would', 'may', 'must', 'let', "let's",
        ]);

        $doDid = $this->menu('Do & did', null, 4);
        $this->words($doDid, [
            'do', 'does', "don't", "doesn't", 'did', "didn't", 'be', 'are', "aren't", 'was', "wasn't", 'were',
            'have', 'has', 'had', "haven't", "isn't", 'being',
        ]);

        $thisThat = $this->menu('This & that', null, 5);
        $this->words($thisThat, [
            'this', 'these', 'those', 'there', "there's", "here's", "that's", "it's", 'same', 'other', 'another',
        ]);

        $amount = $this->menu('Amount', null, 6);
        $this->words($amount, [
            'all', 'any', 'both', 'lot', 'many', 'most', 'much', 'an', 'else', 'only',
        ]);

        $really = $this->menu('Really', null, 7);
        $this->words($really, [
            'really', 'very', 'just', 'even', 'not', 'oh', 'way', 'together', 'like', 'kind',
        ]);

        $pronouns = $this->menu('Pronouns', null, 8);
        $this->words($pronouns, [
            'me', 'myself', 'he', "he's", 'him', 'his', 'she', "she's", 'her', 'we', "we'll", "we're", 'us', 'our',
            'ours', 'they', "they'll", "they're", 'them', 'their', 'your', 'yours', "I'm", "I'll", "you'll", "you're",
            'somebody', 'someone', 'something', 'sometimes', 'somewhere', 'everybody', 'everything',
        ]);

        $questions = $this->menu('Questions', null, 9);
        $this->words($questions, [
            'how', 'where', "where's", 'when', 'which', 'who', 'why', "what's",
        ]);

        $food = $this->menu('Food', null, 10);
        $this->words($food, [
            'hungry', 'eat', 'snack', 'breakfast', 'lunch', 'dinner', 'cookie', 'candy', 'corn', 'bean',
            'bite', 'cup',
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
        $this->words($drinks, ['water', 'juice', 'milk', 'coffee', 'tea', 'drink']);
        $this->phrases($drinks, [
            'I want water',
            'I want juice',
            'I want milk',
            'Can I have a drink?',
        ]);

        $feelings = $this->menu('Feelings', null, 11);
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

        $people = $this->menu('People', null, 12);
        $this->words($people, [
            'mom', 'mommy', 'dad', 'baby', 'boy', 'girl', 'guys', 'man', 'friend', 'teacher', 'doctor',
            'people', 'name',
        ]);
        $this->phrases($people, [
            'I want my mom',
            'I want my dad',
            'Where is my friend?',
            'I need the teacher',
            'I need the doctor',
        ]);

        $places = $this->menu('Places', null, 13);
        $this->words($places, [
            'home', 'school', 'outside', 'bathroom', 'store', 'park', 'house', 'room', 'door', 'hill', 'side',
            'middle', 'top',
        ]);
        $this->phrases($places, [
            'I want to go home',
            'I want to go to school',
            'I want to go outside',
            'I need the bathroom',
            'I want to go to the park',
        ]);

        $actions = $this->menu('Actions', null, 14);
        $this->words($actions, [
            'stop', 'come', 'look', 'wait', 'play', 'sleep', 'back', 'bet', 'buy', 'call', 'came',
            'catch', 'cut', 'fall', 'find', 'fixed', 'fly', 'found', 'get', 'give',
            'gonna', 'hear', 'hold', 'jump', 'know', 'lift', 'made', 'make',
            'mean', 'move', 'open', 'paint', 'pick', 'push', 'put', 'remember', 'ride', 'run', 'said', 'saw', 'say',
            'see', 'show', 'shut', 'sit', 'swing', 'tell', 'threw', 'try', 'turn', 'use',
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

        $describing = $this->menu('Describing', null, 15);
        $this->words($describing, [
            'bad', 'better', 'big', 'different', 'first', 'good', 'great', 'high', 'hot', 'last', 'little', 'long',
            'messy', 'new', 'nice', 'old', 'ready', 'right', 'well', 'whole',
        ]);

        $toys = $this->menu('Toys', null, 16);
        $this->words($toys, ['ball', 'toys', 'tape', 'piece', 'thing', 'stuff']);

        $furniture = $this->menu('Furniture', null, 17);
        $this->words($furniture, ['bed', 'chair', 'box']);

        $vehicles = $this->menu('Vehicles', null, 18);
        $this->words($vehicles, ['car']);

        $nature = $this->menu('Nature', null, 19);
        $this->words($nature, ['tree', 'leaf', 'fire']);

        $time = $this->menu('Time', null, 20);
        $this->words($time, [
            'day', 'time', 'today', 'again', 'already', 'almost', 'never', 'next', 'still', 'yet', 'maybe',
        ]);

        $animals = $this->menu('Animals', null, 21);
        $this->words($animals, ['ant', 'bird', 'bug', 'dog', 'duck', 'horse', 'turtle']);

        $body = $this->menu('Body', null, 22);
        $this->words($body, ['face', 'finger', 'foot', 'hair', 'hand', 'head']);

        $social = $this->menu('Social', null, 23);
        $this->words($social, ['hi', 'hello', 'bye']);

        $colors = $this->menu('Colors', null, 24);
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

        $shapes = $this->menu('Shapes', null, 25);
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

        $numbers = $this->menu('Numbers', null, 26);
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
