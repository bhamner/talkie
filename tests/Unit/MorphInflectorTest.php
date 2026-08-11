<?php

use App\Support\MorphInflector;

test('plural inflection covers regular and irregular forms', function (string $base, string $expected) {
    expect(MorphInflector::apply($base, 'plural'))->toBe($expected);
})->with([
    ['dog', 'dogs'],
    ['box', 'boxes'],
    ['baby', 'babies'],
    ['leaf', 'leaves'],
    ['foot', 'feet'],
    ['bird', 'birds'],
    ['bug', 'bugs'],
]);

test('ing inflection covers drop-e and doubling', function (string $base, string $expected) {
    expect(MorphInflector::apply($base, 'ing'))->toBe($expected);
})->with([
    ['play', 'playing'],
    ['make', 'making'],
    ['run', 'running'],
    ['look', 'looking'],
    ['eat', 'eating'],
]);

test('ed inflection covers regular and irregular past', function (string $base, string $expected) {
    expect(MorphInflector::apply($base, 'ed'))->toBe($expected);
})->with([
    ['want', 'wanted'],
    ['play', 'played'],
    ['like', 'liked'],
    ['try', 'tried'],
    ['go', 'went'],
    ['see', 'saw'],
]);

test('ly inflection covers common adverb forms', function (string $base, string $expected) {
    expect(MorphInflector::apply($base, 'ly'))->toBe($expected);
})->with([
    ['quick', 'quickly'],
    ['real', 'really'],
    ['happy', 'happily'],
    ['nice', 'nicely'],
]);

test('phrase-style joins match expected spoken text', function () {
    $words = [
        MorphInflector::apply('bird', 'plural'),
        MorphInflector::apply('want', 'ed'),
    ];

    expect(implode(' ', $words))->toBe('birds wanted');
});
