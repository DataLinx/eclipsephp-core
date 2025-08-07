<?php

use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Eclipse\Core\Models\User\Role;
use Illuminate\Support\Str;

test('can assign role globally to user', function () {
    $user = User::factory()->create();
    setPermissionsTeamId(null); // Use site ID 0 for global roles
    $role = Role::create(['name' => 'admin_'.Str::random(8), 'guard_name' => 'web']);

    $user->assignGlobalRole($role);

    expect($user->globalRoles()->pluck('name')->contains($role->name))->toBeTrue();
    expect($user->hasGlobalRole($role->name))->toBeTrue();
});

test('can assign same role to specific site', function () {
    $site = Site::factory()->create();
    $user = User::factory()->create();
    setPermissionsTeamId(null);
    $role = Role::create(['name' => 'admin_'.Str::random(8), 'guard_name' => 'web']);

    $user->assignSiteRole($role, $site->id);

    expect($user->siteRoles($site->id)->pluck('name')->contains($role->name))->toBeTrue();
    expect($user->hasSiteRole($role->name, $site->id))->toBeTrue();
});

test('same role can be assigned globally and to specific sites separately', function () {
    $site = Site::factory()->create();
    $user = User::factory()->create();
    setPermissionsTeamId(null);
    $role = Role::create(['name' => 'admin_'.Str::random(8), 'guard_name' => 'web']);

    $user->assignGlobalRole($role);
    $user->assignSiteRole($role, $site->id);

    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    $user->unsetRelation('roles');

    expect($user->globalRoles()->pluck('name')->contains($role->name))->toBeTrue();
    expect($user->siteRoles($site->id)->pluck('name')->contains($role->name))->toBeTrue();
    expect($user->hasGlobalRole($role->name))->toBeTrue();
    expect($user->hasSiteRole($role->name, $site->id))->toBeTrue();
});

test('roles are universally available', function () {
    setPermissionsTeamId(null);
    $role1 = Role::create(['name' => 'manager_'.Str::random(8), 'guard_name' => 'web']);
    $role2 = Role::create(['name' => 'editor_'.Str::random(8), 'guard_name' => 'web']);

    $allRoles = Role::all();

    expect($allRoles->contains('name', $role1->name))->toBeTrue();
    expect($allRoles->contains('name', $role2->name))->toBeTrue();
});

test('sync methods work correctly with universal roles', function () {
    $site = Site::factory()->create();
    $user = User::factory()->create();

    setPermissionsTeamId(null);
    $role1 = Role::create(['name' => 'admin_'.Str::random(8), 'guard_name' => 'web']);
    $role2 = Role::create(['name' => 'moderator_'.Str::random(8), 'guard_name' => 'web']);
    $role3 = Role::create(['name' => 'editor_'.Str::random(8), 'guard_name' => 'web']);

    $user->syncGlobalRoles([$role1->name, $role2->name]);
    $user->syncSiteRoles([$role3->name], $site->id);

    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    $user->unsetRelation('roles');

    expect($user->globalRoles()->pluck('name')->contains($role1->name))->toBeTrue();
    expect($user->globalRoles()->pluck('name')->contains($role2->name))->toBeTrue();
    expect($user->siteRoles($site->id)->pluck('name')->contains($role3->name))->toBeTrue();

    $user->syncGlobalRoles([$role1->name]);

    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    $user->unsetRelation('roles');

    expect($user->globalRoles()->pluck('name')->contains($role1->name))->toBeTrue();
    expect($user->globalRoles()->pluck('name')->contains($role2->name))->toBeFalse();
});

test('can check if user has specific global role', function () {
    $user = User::factory()->create();
    setPermissionsTeamId(null);
    $role = Role::create(['name' => 'admin_'.Str::random(8), 'guard_name' => 'web']);

    $user->assignGlobalRole($role);

    expect($user->hasGlobalRole($role->name))->toBeTrue();
    expect($user->hasGlobalRole('non_existent_role'))->toBeFalse();
});

test('can check if user has specific site role', function () {
    $site1 = Site::factory()->create();
    $site2 = Site::factory()->create();
    $user = User::factory()->create();
    setPermissionsTeamId(null);
    $role = Role::create(['name' => 'editor_'.Str::random(8), 'guard_name' => 'web']);

    $user->assignSiteRole($role, $site1->id);

    expect($user->hasSiteRole($role->name, $site1->id))->toBeTrue();
    expect($user->hasSiteRole($role->name, $site2->id))->toBeFalse();
    expect($user->hasSiteRole('non_existent_role', $site1->id))->toBeFalse();
});

test('user can have same role globally and on specific sites', function () {
    $site1 = Site::factory()->create();
    $site2 = Site::factory()->create();
    $user = User::factory()->create();
    setPermissionsTeamId(null);
    $role = Role::create(['name' => 'manager_'.Str::random(8), 'guard_name' => 'web']);

    $user->assignGlobalRole($role);
    $user->assignSiteRole($role, $site1->id);
    $user->assignSiteRole($role, $site2->id);

    expect($user->hasGlobalRole($role->name))->toBeTrue();
    expect($user->hasSiteRole($role->name, $site1->id))->toBeTrue();
    expect($user->hasSiteRole($role->name, $site2->id))->toBeTrue();
});
