<?php

use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Role;

test('new user automatically gets panel_user role', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    expect($user->hasRole('panel_user'))->toBeTrue();
});

test('user can only access sites they belong to', function () {
    $site1 = Site::factory()->create();
    $site2 = Site::factory()->create();
    $user = User::factory()->create();

    $user->sites()->attach($site1);

    expect($user->canAccessTenant($site1))->toBeTrue();
    expect($user->canAccessTenant($site2))->toBeFalse();
});

test('user is correctly given global role', function () {
    Role::create(['name' => 'global_admin', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->assignRole('global_admin');
    $this->actingAs($user);

    expect($user->hasRole('global_admin'))->toBeTrue();

    $site = Site::factory()->create();
    Filament::setTenant($site);

    expect($user->hasRole('global_admin'))->toBeTrue();

    $site2 = Site::factory()->create();
    Filament::setTenant($site2);

    expect($user->hasRole('global_admin'))->toBeTrue();
});

test('users with global roles are added to new sites', function () {
    $globalRole = Role::create(['name' => 'global_admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($globalRole);
    $this->actingAs($user);

    $site = Site::factory()->create();
    Filament::setTenant($site);

    expect($user->hasRole('global_admin'))->toBeTrue();
});