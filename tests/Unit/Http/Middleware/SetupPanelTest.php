<?php

use Eclipse\Core\Models\Locale;

test('panel is set up correctly', function () {

    $this->migrate();

    // Send a request to load the panel and middleware
    $this->get('/admin/login')->assertStatus(200);

    expect(Config::get('translatable.locales'))
        ->toBeArray()
        ->toContain(...Locale::getAvailableLocales()->pluck('id')->toArray());
});
