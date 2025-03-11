<?php

test('panel login is visible', function () {
    $this->get('/admin/login')->assertStatus(200);
});

test('unauthorized access can be prevented', function () {
    $this->get('/admin')
        ->assertRedirect('admin/login');
});

test('telescope is not visible', function () {
    $this->get('/telescope')->assertStatus(404);
});
