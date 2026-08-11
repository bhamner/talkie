<?php

return [
    [
        'id' => 'device-default',
        'name' => 'Friendly',
        'description' => 'Clear everyday voice for quick communication.',
        'tier' => 'free',
        'provider' => 'device',
        'preview_text' => 'Hello, this is the Friendly voice.',
        'selectable' => true,
    ],
    [
        'id' => 'premium-nova',
        'name' => 'Nova',
        'description' => 'Warm neural voice (Piper). Downloads to your device — works offline.',
        'tier' => 'premium',
        'provider' => 'bundled',
        'engine' => 'piper',
        'model' => null, // e.g. en_US-lessac-medium — set when voice packs ship
        'preview_text' => 'Hello, this is Nova.',
        'selectable' => false,
    ],
    [
        'id' => 'premium-harbor',
        'name' => 'Harbor',
        'description' => 'Calm neural voice (Kokoro). Downloads to your device — works offline.',
        'tier' => 'premium',
        'provider' => 'bundled',
        'engine' => 'kokoro',
        'model' => null,
        'preview_text' => 'Hello, this is Harbor.',
        'selectable' => false,
    ],
    [
        'id' => 'premium-spark',
        'name' => 'Spark',
        'description' => 'Bright neural voice (Piper). Downloads to your device — works offline.',
        'tier' => 'premium',
        'provider' => 'bundled',
        'engine' => 'piper',
        'model' => null,
        'preview_text' => 'Hello, this is Spark.',
        'selectable' => false,
    ],
];
