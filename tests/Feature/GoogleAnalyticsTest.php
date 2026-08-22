<?php

use Database\Seeders\BoardTemplateSeeder;

test('ga4 snippet is omitted when ga4 id is not configured', function () {
    $this->seed(BoardTemplateSeeder::class);

    config(['services.ga4.id' => null]);

    $this->get('/board')
        ->assertOk()
        ->assertDontSee('gtag/js', false);
});

test('ga4 snippet is included when ga4 id is configured', function () {
    $this->seed(BoardTemplateSeeder::class);

    config(['services.ga4.id' => 'G-TESTID12']);

    $this->get('/board')
        ->assertOk()
        ->assertSee('gtag/js?id=G-TESTID12', false)
        ->assertSee('gtag(\'config\', "G-TESTID12")', false);
});
