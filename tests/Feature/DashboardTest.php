<?php

use App\Models\User;

test('guests are redirected to personalize', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/personalize');
});

test('authenticated users are redirected from dashboard through onboarding gate', function () {
    $user = User::factory()->create([
        'preferred_name' => null,
    ]);
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertRedirect(route('onboarding.gate', absolute: false));
});
