<?php

use App\Support\TalkieVoices;

test('signed in web users can select piper and the device voice', function () {
    $user = createOnboardedUser();

    $ids = TalkieVoices::selectableIds($user);

    expect($ids)->toBe(['device-default', 'premium-nova']);
});

test('guests cannot select neural voices', function () {
    $ids = TalkieVoices::selectableIds(null);

    expect($ids)->toBe(['device-default']);
});

test('piper uses the libritts piper model', function () {
    $piper = TalkieVoices::find('premium-nova');

    expect($piper['name'])->toBe('Piper')
        ->and($piper['description'])->toBe('High Quality Neural TTS voice')
        ->and($piper['engine'])->toBe('piper')
        ->and($piper['model'])->toBe('en_US-libritts_r-medium')
        ->and($piper['platforms'])->toContain('web');
});

test('voice settings page offers piper and not former app-only voices', function () {
    $user = createOnboardedUser();

    $this->actingAs($user)
        ->get('/settings/voice')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/Voice')
            ->has('voices', 2)
            ->where('voices.1.id', 'premium-nova')
            ->where('voices.1.name', 'Piper')
            ->where('voices.1.selectable', true)
            ->where('voices.1.provider', 'bundled')
            ->where('voices.1.engine', 'piper')
            ->where('voices.1.model', 'en_US-libritts_r-medium')
        );
});
