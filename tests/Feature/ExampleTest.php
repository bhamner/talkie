<?php

test('home redirects to the public board', function () {
    $response = $this->get('/');

    $response->assertRedirect('/board');
});
