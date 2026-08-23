<?php

test('legal pages are public and use the talkie.kids website', function (string $path, string $heading) {
    $this->get($path)
        ->assertSuccessful()
        ->assertSee($heading, false)
        ->assertSee('talkie.kids', false)
        ->assertDontSee('hamnercreative.com', false)
        ->assertDontSee('2052422057', false)
        ->assertDontSee('3935 Edgebrook', false)
        ->assertDontSee('Northport', false)
        ->assertDontSee('<strong>Hamner Creative </strong>', false)
        ->assertSee(route('privacy'), false)
        ->assertSee(route('terms'), false)
        ->assertSee(route('cookies'), false);
})->with([
    'privacy' => ['/privacy', 'PRIVACY POLICY'],
    'cookies' => ['/cookies', 'COOKIE POLICY'],
    'terms' => ['/terms', 'TERMS AND CONDITIONS'],
]);

test('privacy policy points account data requests at the profile settings page', function () {
    $this->get('/privacy')
        ->assertSuccessful()
        ->assertDontSee('talkie.kids/user', false)
        ->assertSee('https://www.talkie.kids/settings/profile', false);
});
