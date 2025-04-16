<?php

use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Filament\Facades\Filament;

test('get filament avatar url', function () {
    $user = User::factory()->create();

    expect($user->getFilamentAvatarUrl())->toBeNull();

    // Create dummy png file in tmp dir
    $tmp_file = tempnam(sys_get_temp_dir(), 'avatar');
    file_put_contents($tmp_file, 'test');

    // Set user's avatar
    $user->addMedia($tmp_file)
        ->preservingOriginal()
        ->toMediaCollection('avatars');

    $user->refresh();

    expect($user->getMedia('avatars'))->toHaveCount(1)
        ->and($user->getFilamentAvatarUrl())->not()->toBeNull();
});

test('can access tenant', function () {
    // Create 2 tenant sites
    $site_1 = Site::factory()->create();
    $site_2 = Site::factory()->create();

    // Create user
    $user = User::factory()->create();

    // Associate user with tenant
    $user->sites()->attach($site_1);

    expect($user->canAccessTenant($site_1))->toBeTrue()
        ->and($user->canAccessTenant($site_2))->toBeFalse();
});

test('get tenants', function () {
    // Create 2 tenant sites
    $site_1 = Site::factory()->create(['is_active' => true]);
    $site_2 = Site::factory()->create(['is_active' => false]);

    // Create user
    $user = User::factory()->create();

    // Associate user with tenant
    $user->sites()->attach([$site_1, $site_2]);

    expect($user->getTenants(Filament::getPanel()))->toHaveCount(1);
});

test('can access panel', function () {
    $user = User::factory()->create();

    expect($user->canAccessPanel(Filament::getPanel()))->toBeTrue();
});

test('booted', function () {
    $user = User::factory()->create();

    expect($user->name)->toBe("$user->first_name $user->last_name");
});
