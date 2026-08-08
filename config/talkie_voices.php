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
        'description' => 'Warm AI voice with natural pacing.',
        'tier' => 'premium',
        'provider' => 'cloud',
        'preview_text' => 'Hello, this is Nova.',
        'selectable' => false,
    ],
    [
        'id' => 'premium-harbor',
        'name' => 'Harbor',
        'description' => 'Calm AI voice suited for longer phrases.',
        'tier' => 'premium',
        'provider' => 'cloud',
        'preview_text' => 'Hello, this is Harbor.',
        'selectable' => false,
    ],
    [
        'id' => 'premium-spark',
        'name' => 'Spark',
        'description' => 'Bright AI voice with energetic delivery.',
        'tier' => 'premium',
        'provider' => 'cloud',
        'preview_text' => 'Hello, this is Spark.',
        'selectable' => false,
    ],
];
