<?php

return [
    [
        'id' => 'device-default',
        'name' => 'Friendly',
        'description' => 'Clear everyday voice for quick communication.',
        'tier' => 'free',
        'provider' => 'device',
        'platforms' => ['web', 'mobile'],
        'preview_text' => 'Hello, this is the Friendly voice.',
        'selectable' => true,
    ],
    [
        'id' => 'premium-nova',
        'name' => 'Nova',
        'description' => 'Warm Piper voice. Sign in on the web to use it; the same voice ships in the Talkie app.',
        'tier' => 'premium',
        'provider' => 'bundled',
        'engine' => 'piper',
        'platforms' => ['web', 'mobile'],
        'model' => null, // e.g. en_US-lessac-medium — set when Piper packs ship
        'preview_text' => 'Hello, this is Nova.',
        // Selectable after Phase 3 for signed-in web (and all paid-app users).
        'selectable' => false,
    ],
    [
        'id' => 'premium-harbor',
        'name' => 'Harbor',
        'description' => 'Calm Kokoro voice. Included in the Talkie app — too large for the browser.',
        'tier' => 'premium',
        'provider' => 'bundled',
        'engine' => 'kokoro',
        'platforms' => ['mobile'],
        'model' => null,
        'preview_text' => 'Hello, this is Harbor.',
        'selectable' => false,
    ],
    [
        'id' => 'premium-spark',
        'name' => 'Spark',
        'description' => 'Bright Piper voice. Sign in on the web to use it; the same voice ships in the Talkie app.',
        'tier' => 'premium',
        'provider' => 'bundled',
        'engine' => 'piper',
        'platforms' => ['web', 'mobile'],
        'model' => null,
        'preview_text' => 'Hello, this is Spark.',
        // Selectable after Phase 3 for signed-in web (and all paid-app users).
        'selectable' => false,
    ],
];
