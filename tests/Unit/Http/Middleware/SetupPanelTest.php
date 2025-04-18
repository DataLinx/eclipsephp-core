<?php

use Eclipse\Core\Models\Locale;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;

test('panel is set up correctly', function () {

    // Send a request to load the panel and middleware
    $this->get('/admin/login')->assertStatus(200);

    /** @var \Filament\SpatieLaravelTranslatablePlugin $plugin */
    $plugin = Filament::getPanel()->getPlugin('spatie-laravel-translatable');

    expect($plugin)
        ->toBeInstanceOf(Plugin::class)
        ->and($plugin->getDefaultLocales())
        ->toContain(...Locale::getAvailableLocales()->pluck('id')->toArray());
});
