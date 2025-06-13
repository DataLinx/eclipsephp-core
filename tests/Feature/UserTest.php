<?php

use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;

test('new user automatically gets panel_user role', function () {});

test('user from seeder gets panel_user role', function () {});

test('user can only access sites they belong to', function () {
    $site1 = Site::factory()->create();
    $site2 = Site::factory()->create();
    $user = User::factory()->create();

    $user->sites()->attach($site1);

    expect($user->canAccessTenant($site1))->toBeTrue();
    expect($user->canAccessTenant($site2))->toBeFalse();
});
