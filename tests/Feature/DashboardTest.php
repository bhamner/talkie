<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users are redirected from dashboard through onboarding gate', function () {
    $user = User::factory()->create([
        'preferred_name' => null,
    ]);
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertRedirect(route('onboarding.gate', absolute: false));
});
