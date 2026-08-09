<?php

test('profile page is displayed', function () {
    $user = createOnboardedUser();

    $response = $this
        ->actingAs($user)
        ->get('/settings/profile');

    $response->assertOk();
});

test('profile name can be updated but email is not mass assigned', function () {
    $user = createOnboardedUser([
        'email' => 'owner@example.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'name' => 'Test User',
            'email' => 'attacker@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/profile');

    $user->refresh();

    expect($user->name)->toBe('Test User')
        ->and($user->email)->toBe('owner@example.com');
});

test('user can delete their account by confirming email', function () {
    $user = createOnboardedUser([
        'email' => 'owner@example.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->delete('/settings/profile', [
            'confirmation' => 'owner@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('account deletion requires the users email', function () {
    $user = createOnboardedUser([
        'email' => 'owner@example.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->from('/settings/profile')
        ->delete('/settings/profile', [
            'confirmation' => 'wrong@example.com',
        ]);

    $response
        ->assertSessionHasErrors('confirmation')
        ->assertRedirect('/settings/profile');

    expect($user->fresh())->not->toBeNull();
});
