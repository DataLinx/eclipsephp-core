<?php

test('panel login is visible', function () {
    $this->get('/admin/login')->assertStatus(200);
});

test('telescope is not visible', function () {
    $this->get('/telescope')->assertStatus(404);
});
