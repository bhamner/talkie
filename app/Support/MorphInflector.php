<?php

namespace App\Support;

/**
 * English inflection helpers for board morphology tiles.
 * Keep in sync with resources/js/lib/morphPhrase.ts.
 */
class MorphInflector
{
    /**
     * @var array<string, string>
     */
    private const PLURAL_EXCEPTIONS = [
        'child' => 'children',
        'foot' => 'feet',
        'leaf' => 'leaves',
        'mouse' => 'mice',
        'person' => 'people',
        'tooth' => 'teeth',
        'man' => 'men',
        'woman' => 'women',
    ];

    /**
     * @var array<string, string>
     */
    private const PAST_EXCEPTIONS = [
        'go' => 'went',
        'come' => 'came',
        'see' => 'saw',
        'say' => 'said',
        'get' => 'got',
        'make' => 'made',
        'find' => 'found',
        'run' => 'ran',
        'sit' => 'sat',
        'fall' => 'fell',
        'throw' => 'threw',
        'give' => 'gave',
        'know' => 'knew',
        'take' => 'took',
        'eat' => 'ate',
    ];

    public static function apply(string $base, string $kind): string
    {
        $trimmed = trim($base);

        if ($trimmed === '') {
            return $trimmed;
        }

        $lower = strtolower($trimmed);

        return match ($kind) {
            'plural' => self::plural($trimmed, $lower),
            'ing' => self::ing($trimmed, $lower),
            'ed' => self::ed($trimmed, $lower),
            'ly' => self::ly($trimmed, $lower),
            'possessive' => self::possessive($trimmed, $lower),
            default => $trimmed,
        };
    }

    private static function possessive(string $trimmed, string $lower): string
    {
        if (str_ends_with($lower, 's')) {
            return $trimmed."'";
        }

        return $trimmed."'s";
    }

    private static function plural(string $trimmed, string $lower): string
    {
        if (isset(self::PLURAL_EXCEPTIONS[$lower])) {
            return self::PLURAL_EXCEPTIONS[$lower];
        }

        if (self::endsWithConsonantY($lower)) {
            return substr($trimmed, 0, -1).'ies';
        }

        if (self::endsWithSibilant($lower) || str_ends_with($lower, 'o')) {
            return $trimmed.'es';
        }

        if (str_ends_with($lower, 'f')) {
            return substr($trimmed, 0, -1).'ves';
        }

        if (str_ends_with($lower, 'fe')) {
            return substr($trimmed, 0, -2).'ves';
        }

        return $trimmed.'s';
    }

    private static function ing(string $trimmed, string $lower): string
    {
        if (str_ends_with($lower, 'ie')) {
            return substr($trimmed, 0, -2).'ying';
        }

        if (str_ends_with($lower, 'e') && ! str_ends_with($lower, 'ee')) {
            return substr($trimmed, 0, -1).'ing';
        }

        if (self::endsWithConsonantVowelConsonant($lower)) {
            return $trimmed.substr($trimmed, -1).'ing';
        }

        return $trimmed.'ing';
    }

    private static function ed(string $trimmed, string $lower): string
    {
        if (isset(self::PAST_EXCEPTIONS[$lower])) {
            return self::PAST_EXCEPTIONS[$lower];
        }

        if (str_ends_with($lower, 'e')) {
            return $trimmed.'d';
        }

        if (self::endsWithConsonantY($lower)) {
            return substr($trimmed, 0, -1).'ied';
        }

        if (self::endsWithConsonantVowelConsonant($lower)) {
            return $trimmed.substr($trimmed, -1).'ed';
        }

        return $trimmed.'ed';
    }

    private static function ly(string $trimmed, string $lower): string
    {
        if (self::endsWithConsonantY($lower)) {
            return substr($trimmed, 0, -1).'ily';
        }

        if (str_ends_with($lower, 'le')) {
            return substr($trimmed, 0, -1).'y';
        }

        if (str_ends_with($lower, 'ic')) {
            return $trimmed.'ally';
        }

        return $trimmed.'ly';
    }

    private static function endsWithConsonantY(string $lower): bool
    {
        return (bool) preg_match('/[^aeiou]y$/', $lower);
    }

    private static function endsWithSibilant(string $lower): bool
    {
        return (bool) preg_match('/(?:s|ss|sh|ch|x|z)$/', $lower);
    }

    private static function endsWithConsonantVowelConsonant(string $lower): bool
    {
        return (bool) preg_match('/[^aeiou][aeiou][^aeiouwxy]$/', $lower);
    }
}
