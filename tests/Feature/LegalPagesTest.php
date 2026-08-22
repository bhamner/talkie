<?php

test('legal pages are public and use the talkie.kids website', function (string $path, string $heading) {
    $this->get($path)
        ->assertSuccessful()
        ->assertSee($heading, false)
        ->assertSee('talkie.kids', false)
        ->assertDontSee('hamnercreative.com', false)
        ->assertSee(route('privacy'), false)
        ->assertSee(route('terms'), false)
        ->assertSee(route('cookies'), false);
})->with([
    'privacy' => ['/privacy', 'PRIVACY POLICY'],
    'cookies' => ['/cookies', 'COOKIE POLICY'],
    'terms' => ['/terms', 'TERMS AND CONDITIONS'],
]);
