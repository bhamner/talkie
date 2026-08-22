<?php

use App\Models\User;
use App\Models\Word;
use Database\Seeders\DatabaseSeeder;

test('database seeder seeds the board template without creating users', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->count())->toBe(0)
        ->and(Word::query()->template()->count())->toBeGreaterThan(0);
});
