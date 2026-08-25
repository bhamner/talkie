<?php

test('voice settings page can be rendered', function () {
    $user = createOnboardedUser();

    $this->actingAs($user)
        ->get('/settings/voice')
        ->assertOk();
});

test('voice settings can be updated', function () {
    $user = createOnboardedUser();

    $this->actingAs($user)
        ->put('/settings/voice', [
            'voice_id' => 'device-default',
            'voice_uri' => 'com.apple.voice.compact.en-US.Samantha',
            'voice_name' => 'Friendly',
        ])
        ->assertRedirect();

    $user->refresh();

    expect($user->settings->voice_id)->toBe('device-default')
        ->and($user->settings->voice_uri)->toBe('com.apple.voice.compact.en-US.Samantha')
        ->and($user->settings->voice_name)->toBe('Friendly');
});

test('signed in users can select the web libritts voice', function () {
    $user = createOnboardedUser();

    $this->actingAs($user)
        ->put('/settings/voice', [
            'voice_id' => 'premium-nova',
        ])
        ->assertRedirect();

    $user->refresh();

    expect($user->settings->voice_id)->toBe('premium-nova')
        ->and($user->settings->voice_name)->toBe('Piper');
});

test('web users cannot select voices that are not in the catalog', function () {
    $user = createOnboardedUser();

    $this->actingAs($user)
        ->put('/settings/voice', [
            'voice_id' => 'premium-harbor',
        ])
        ->assertSessionHasErrors('voice_id');

    $this->actingAs($user)
        ->put('/settings/voice', [
            'voice_id' => 'premium-spark',
        ])
        ->assertSessionHasErrors('voice_id');
});
