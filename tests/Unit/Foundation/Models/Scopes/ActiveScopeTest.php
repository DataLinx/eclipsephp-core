<?php

use Eclipse\Common\Foundation\Models\Scopes\ActiveScope;
use Workbench\App\Models\Product;

test('active scope filters inactive records', function () {
    Product::factory()->create(['name' => 'Active Product', 'is_active' => true]);
    Product::factory()->create(['name' => 'Inactive Product', 'is_active' => false]);

    expect(Product::count())->toBe(1)
        ->and(Product::first()->name)->toBe('Active Product');
});

test('active scope can be disabled with withoutGlobalScope', function () {
    Product::factory()->create(['is_active' => true]);
    Product::factory()->inactive()->create();

    expect(Product::count())->toBe(1)
        ->and(Product::withoutGlobalScope(ActiveScope::class)->count())->toBe(2);
});

test('active scope only returns active records by default', function () {
    $activeProduct = Product::factory()->create(['is_active' => true]);
    $inactiveProduct = Product::factory()->inactive()->create();

    expect(Product::where('id', $activeProduct->id)->count())->toBe(1)
        ->and(Product::where('id', $inactiveProduct->id)->count())->toBe(0);
});

test('active scope allows querying inactive records without scope', function () {
    $inactiveProduct = Product::factory()->inactive()->create();

    expect(Product::withoutGlobalScope(ActiveScope::class)->where('id', $inactiveProduct->id)->count())->toBe(1);
});
