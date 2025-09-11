<?php

use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Eclipse\Core\View\Components\BrandWithTenantSwitcher;

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

test('brand component dropdown behavior depends on frontend and tenant availability', function () {
    $component = new BrandWithTenantSwitcher;

    $hasFrontend = $component->hasFrontend();
    $canSwitchTenants = $component->canSwitchTenants();
    $shouldShowDropdown = $component->shouldShowDropdown();

    expect($shouldShowDropdown)->toBe($hasFrontend || $canSwitchTenants);

    expect($hasFrontend)->toBeBool();
    expect($canSwitchTenants)->toBeBool();
    expect($shouldShowDropdown)->toBeBool();
});

test('tenant switcher logic handles multiple and single tenant scenarios', function () {
    config(['eclipse.multi_site' => true]);

    $user = User::factory()->create();
    $site1 = Site::factory()->create(['name' => 'Primary Site']);
    $site2 = Site::factory()->create(['name' => 'Secondary Site']);

    $user->sites()->attach([$site1->id, $site2->id]);

    expect($user->sites)->toHaveCount(2);
    expect($user->sites->pluck('name'))->toContain('Primary Site', 'Secondary Site');

    $singleSiteUser = User::factory()->create();
    $singleSite = Site::factory()->create(['name' => 'Only Site']);
    $singleSiteUser->sites()->attach($singleSite->id);

    expect($singleSiteUser->sites)->toHaveCount(1);
    expect($singleSiteUser->sites->first()->name)->toBe('Only Site');
});

test('brand component renders app name correctly', function () {
    $component = new BrandWithTenantSwitcher;

    $appName = $component->getAppName();

    expect($appName)->toBe(config('app.name'));
});
