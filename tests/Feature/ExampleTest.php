<?php

test('panel login is visible', function () {
    $this->get('/admin/login')->assertStatus(200);
});
