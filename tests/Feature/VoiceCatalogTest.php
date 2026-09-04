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

test('guests speak with the device voice', function () {
    expect(TalkieVoices::current(null)['provider'])->toBe('device')
        ->and(TalkieVoices::current(null)['engine'])->toBeNull();
});

test('native app guests speak with piper', function () {
    expect(TalkieVoices::current(null, true))->toMatchArray([
        'id' => 'premium-nova',
        'name' => 'Piper',
        'provider' => 'bundled',
        'engine' => 'piper',
        'model' => 'en_US-libritts_r-medium',
        'speaker_id' => 0,
    ]);
});

test('native apps keep a saved device voice', function () {
    $user = createOnboardedUser();

    expect(TalkieVoices::current($user, true)['provider'])->toBe('device')
        ->and(TalkieVoices::current($user, true)['engine'])->toBeNull()
        ->and(TalkieVoices::current($user, true)['id'])->toBe('device-default');
});

test('talkie native user agents are detected', function () {
    expect(TalkieVoices::isNativeUserAgent('Mozilla/5.0 Chrome/124.0.0.0 Mobile Safari/537.36 TalkieNative/android'))->toBeTrue()
        ->and(TalkieVoices::isNativeUserAgent('Mozilla/5.0 TalkieNative/ios'))->toBeTrue()
        ->and(TalkieVoices::isNativeUserAgent('Mozilla/5.0 (Linux; Android 14; Pixel 8) Chrome/124.0.0.0 Mobile Safari/537.36'))->toBeFalse();
});

test('native client header is treated as the native app', function () {
    request()->headers->set('X-Talkie-Client', 'ios');

    expect(TalkieVoices::isNativeRequest())->toBeTrue()
        ->and(TalkieVoices::current(null)['engine'])->toBe('piper');
});

test('piper uses the libritts piper model', function () {
    $piper = TalkieVoices::find('premium-nova');

    expect($piper['name'])->toBe('Piper')
        ->and($piper['description'])->toBe('High Quality Neural TTS voice')
        ->and($piper['engine'])->toBe('piper')
        ->and($piper['model'])->toBe('en_US-libritts_r-medium')
        ->and($piper['speaker_id'])->toBe(0)
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
            ->where('voices.0.id', 'device-default')
            ->where('voices.0.name', 'System')
            ->where('voices.0.description', 'the default voice on your device')
            ->where('voices.1.id', 'premium-nova')
            ->where('voices.1.name', 'Piper')
            ->where('voices.1.selectable', true)
            ->where('voices.1.provider', 'bundled')
            ->where('voices.1.engine', 'piper')
            ->where('voices.1.model', 'en_US-libritts_r-medium')
        );
});
