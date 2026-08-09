<?php

use App\Models\User;

test('login and register routes redirect to personalize', function () {
    $this->get('/login')->assertRedirect('/personalize');
    $this->get('/register')->assertRedirect('/personalize');
});

test('password login is not available', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/personalize');

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
