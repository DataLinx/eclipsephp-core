<?php

use Eclipse\Core\Models\Locale;
use Eclipse\Core\Models\Scopes\ActiveScope;

test('active scope works', function () {

    // Create Locale with is_active = 0
    Locale::factory()->create(['id' => 'xx', 'is_active' => 0]);

    // Test scope being applied
    expect(Locale::where('id', 'xx')->count())->toBe(0);

    // Test scope not being applied
    expect(Locale::withoutGlobalScope(ActiveScope::class)->where('id', 'xx')->count())->toBe(1);
});
