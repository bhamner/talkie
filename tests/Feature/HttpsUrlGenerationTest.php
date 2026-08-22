<?php

use Illuminate\Support\Facades\URL;

test('https app url produces https absolute urls for assets and routes', function () {
    URL::forceRootUrl('https://talkie-app.ondigitalocean.app');
    URL::forceScheme('https');

    expect(url('/board'))->toBe('https://talkie-app.ondigitalocean.app/board')
        ->and(asset('build/manifest.json'))->toStartWith('https://');
});
