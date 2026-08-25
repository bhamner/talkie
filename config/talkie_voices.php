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
    ],
    [
        'id' => 'premium-nova',
        'name' => 'Piper',
        'description' => 'High Quality Neural TTS voice',
        'tier' => 'neural',
        'provider' => 'bundled',
        'engine' => 'piper',
        'platforms' => ['web', 'mobile'],
        'model' => 'en_US-libritts_r-medium',
        'speaker_id' => 0,
        'preview_text' => 'Hello, this is Piper.',
    ],
];
