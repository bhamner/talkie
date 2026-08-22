<?php

namespace App\Support;

/**
 * AAC core vocabulary expectations for the starter board.
 *
 * Scored words belong on the home board (highest frequency first).
 * Preschool list is Marvin, Beukelman & Bilyeu (filtered for Talkie tiles).
 */
class CoreVocabulary
{
    /**
     * Highest-use words with relative frequency scores (desc).
     * "all done/finished" is represented as both done and finished.
     *
     * @return list<string>
     */
    public static function scoredHomeWords(): array
    {
        return [
            'I',
            'no',
            'yes',
            'my',
            'the',
            'want',
            'is',
            'it',
            'that',
            'a',
            'go',
            'mine',
            'you',
            'what',
            'on',
            'in',
            'here',
            'more',
            'out',
            'off',
            'some',
            'help',
            'done',
            'finished',
        ];
    }

    /**
     * Extra AAC staples kept on home after the scored core.
     *
     * @return list<string>
     */
    public static function additionalHomeWords(): array
    {
        return [
            'need',
            'please',
            'not',
        ];
    }

    /**
     * @return list<string>
     */
    public static function homeWords(): array
    {
        return array_values(array_unique([
            ...self::scoredHomeWords(),
            ...self::additionalHomeWords(),
        ]));
    }

    /**
     * Fillers / non-words / placeholders removed from the preschool list.
     *
     * @return list<string>
     */
    public static function excludedPreschoolWords(): array
    {
        return [
            'ah',
            'huh',
            'hum',
            'um',
            'ya',
            'cause',
            'named',
            'pet name',
            'box',
            'stuff',
        ];
    }

    /**
     * Preschool study words that map onto an existing tile label.
     *
     * @return array<string, string>
     */
    public static function preschoolLabelAliases(): array
    {
        return [
            'ya' => 'yes',
            'ok' => 'okay',
            'cause' => 'because',
            'one' => '1',
            'two' => '2',
            'three' => '3',
            'five' => '5',
            // Plural / conjugated study forms → singular lemma (+ grammar tiles)
            'birds' => 'bird',
            'bugs' => 'bug',
            'girls' => 'girl',
            'hands' => 'hand',
            'leaves' => 'leaf',
            'things' => 'thing',
            'trees' => 'tree',
            'turtles' => 'turtle',
            'comes' => 'come',
            'looking' => 'look',
            'eating' => 'eat',
            'jumping' => 'jump',
            'jumped' => 'jump',
            'getting' => 'get',
            'gets' => 'get',
            'goes' => 'go',
            'doing' => 'do',
            'wanted' => 'want',
            'used' => 'use',
            'trying' => 'try',
            'gonna' => 'going',
            'fixed' => 'fix',
            'toys' => 'toy',
            "what's" => 'what',
            "where's" => 'where',
            "can't" => 'can',
            "couldn't" => 'could',
            "won't" => 'would',
            "let's" => 'let',
            "don't" => 'do',
            "doesn't" => 'does',
            "didn't" => 'did',
            "aren't" => 'are',
            "wasn't" => 'was',
            "haven't" => 'have',
            "isn't" => 'is',
            'being' => 'be',
            "i'm" => 'I',
            "i'll" => 'I',
            "you'll" => 'you',
            "you're" => 'you',
            "he's" => 'he',
            "she's" => 'she',
            "we'll" => 'we',
            "we're" => 'we',
            "they'll" => 'they',
            "they're" => 'they',
            "it's" => 'it',
            "that's" => 'that',
            "there's" => 'there',
            "here's" => 'here',
            'ours' => 'our',
            'yours' => 'your',
        ];
    }

    /**
     * Filtered Marvin / Beukelman / Bilyeu preschool words that should exist as tiles
     * (or via alias mapping in preschoolLabelAliases).
     *
     * @return list<string>
     */
    public static function preschoolSourceWords(): array
    {
        return [
            'about', 'after', 'again', 'all', 'almost', 'already', 'also', 'an', 'and', 'another', 'ant', 'any',
            'are', "aren't", 'around', 'as', 'at', 'away', 'baby', 'back', 'bad', 'ball', 'bathroom', 'be', 'bean',
            'because', 'bed', 'before', 'being', 'bet', 'better', 'big', 'bird', 'birds', 'bite', 'black', 'blue',
            'both', 'box', 'boy', 'bugs', 'but', 'buy', 'by', 'bye', 'call', 'came', 'can', "can't", 'candy', 'car',
            'catch', 'chair', 'come', 'comes', 'cookie', 'corn', 'could', "couldn't", 'cup', 'cut', 'day', 'did',
            "didn't", 'different', 'do', 'doctor', 'does', "doesn't", 'dog', 'doing', "don't", 'done', 'door', 'down',
            'drink', 'duck', 'eat', 'eating', 'else', 'even', 'everybody', 'everything', 'face', 'fall', 'find',
            'finger', 'fire', 'first', 'five', 'fixed', 'fly', 'foot', 'for', 'found', 'from', 'get', 'gets', 'getting',
            'girl', 'girls', 'give', 'go', 'goes', 'going', 'gonna', 'good', 'great', 'green', 'had', 'hair',
            'hand', 'hands', 'has', 'have', "haven't", 'he', "he's", 'head', 'hear', 'hello', 'help', 'her', 'here',
            "here's", 'hi', 'high', 'hill', 'him', 'his', 'hold', 'home', 'horse', 'hot', 'house', 'how', 'I', "I'll",
            "I'm", 'if', 'in', 'inside', 'is', "isn't", 'it', "it's", 'juice', 'jump', 'jumped', 'jumping', 'just',
            'kind', 'know', 'last', 'leaves', 'let', "let's", 'lift', 'like', 'little', 'long', 'look', 'looking',
            'lot', 'lunch', 'made', 'make', 'man', 'many', 'may', 'maybe', 'me', 'mean', 'messy', 'middle', 'mine',
            'mom', 'more', 'most', 'move', 'much', 'must', 'my', 'myself', 'name', 'need', 'never', 'new',
            'next', 'nice', 'no', 'not', 'of', 'off', 'oh', 'ok', 'old', 'on', 'one', 'only', 'open', 'or', 'other',
            'our', 'ours', 'out', 'over', 'paint', 'people', 'pick', 'piece', 'play', 'please', 'push', 'put', 'ready',
            'really', 'red', 'remember', 'ride', 'right', 'room', 'run', 'said', 'same', 'saw', 'say', 'see', 'she',
            "she's", 'show', 'shut', 'side', 'sit', 'so', 'some', 'somebody', 'someone', 'something', 'sometimes',
            'somewhere', 'still', 'stop', 'stuff', 'swing', 'tape', 'tell', 'than', 'that', "that's", 'the', 'their',
            'them', 'then', 'there', "there's", 'these', 'they', "they'll", "they're", 'thing', 'things', 'this',
            'those', 'three', 'threw', 'through', 'time', 'to', 'today', 'together', 'too', 'top', 'toys', 'trees',
            'try', 'trying', 'turn', 'turtles', 'two', 'up', 'us', 'use', 'used', 'very', 'wait', 'want', 'wanted',
            'was', "wasn't", 'watch', 'water', 'way', 'we', "we'll", "we're", 'well', 'went', 'were', 'what', "what's",
            'when', 'where', "where's", 'which', 'while', 'who', 'whole', 'why', 'with', "won't", 'would', 'yes',
            'yet', 'you', "you'll", "you're", 'your', 'yours',
        ];
    }

    /**
     * Labels that must exist on the board after filtering and alias mapping.
     *
     * @return list<string>
     */
    public static function requiredBoardLabels(): array
    {
        $labels = [];

        foreach (self::preschoolSourceWords() as $word) {
            $key = strtolower($word);

            if (in_array($key, self::excludedPreschoolWords(), true)) {
                continue;
            }

            if (isset(self::preschoolLabelAliases()[$key])) {
                $labels[] = self::preschoolLabelAliases()[$key];

                continue;
            }

            $labels[] = $word;
        }

        foreach (self::homeWords() as $word) {
            $labels[] = $word;
        }

        $unique = [];

        foreach ($labels as $label) {
            $unique[strtolower($label)] = $label;
        }

        ksort($unique);

        return array_values($unique);
    }
}
