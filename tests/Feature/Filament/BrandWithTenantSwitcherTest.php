<?php

test('brand component renders without tenants', function () {
    $response = $this->get('/admin/login');

    $response->assertStatus(200);
    $response->assertSee(config('app.name'));
});

test('brand component renders with multi-site enabled', function () {
    config(['eclipse.multi_site' => true]);

    $response = $this->get('/admin/login');
    $response->assertStatus(200);
    $response->assertSee(config('app.name'));
});

test('brand component shows app name consistently', function () {
    config(['eclipse.multi_site' => true]);

    $response = $this->get('/admin/login');

    $response->assertStatus(200);
    $response->assertSee(config('app.name'));
});
