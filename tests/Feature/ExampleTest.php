<?php

test('the application requires authentication before showing the dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
