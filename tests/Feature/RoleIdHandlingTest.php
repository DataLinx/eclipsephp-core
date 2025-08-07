<?php

use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Eclipse\Core\Models\User\Role;
use Illuminate\Support\Str;

test('can sync global roles using role IDs', function () {
    $user = User::factory()->create();
    setPermissionsTeamId(null);

    $role1 = Role::create(['name' => 'admin_'.Str::random(8), 'guard_name' => 'web']);
    $role2 = Role::create(['name' => 'moderator_'.Str::random(8), 'guard_name' => 'web']);

    $user->syncGlobalRoles([$role1->id, $role2->id]);

    expect($user->globalRoles()->pluck('name')->contains($role1->name))->toBeTrue();
    expect($user->globalRoles()->pluck('name')->contains($role2->name))->toBeTrue();
});

test('can sync site roles using role IDs', function () {
    $site = Site::factory()->create();
    $user = User::factory()->create();
    setPermissionsTeamId(null);

    $role1 = Role::create(['name' => 'editor_'.Str::random(8), 'guard_name' => 'web']);
    $role2 = Role::create(['name' => 'viewer_'.Str::random(8), 'guard_name' => 'web']);

    $user->syncSiteRoles([$role1->id, $role2->id], $site->id);

    expect($user->siteRoles($site->id)->pluck('name')->contains($role1->name))->toBeTrue();
    expect($user->siteRoles($site->id)->pluck('name')->contains($role2->name))->toBeTrue();
});

test('can assign individual roles using role IDs', function () {
    $site = Site::factory()->create();
    $user = User::factory()->create();
    setPermissionsTeamId(null);

    $role = Role::create(['name' => 'manager_'.Str::random(8), 'guard_name' => 'web']);

    $user->assignGlobalRole($role->id);
    $user->assignSiteRole($role->id, $site->id);

    expect($user->hasGlobalRole($role->name))->toBeTrue();
    expect($user->hasSiteRole($role->name, $site->id))->toBeTrue();
});
