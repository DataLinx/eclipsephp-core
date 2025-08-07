<?php

use Eclipse\Core\Filament\Resources\UserResource;
use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Eclipse\Core\Models\User\Role;
use Illuminate\Support\Str;

use function Pest\Livewire\livewire;

test('super admin can manage user roles', function () {
    $this->migrate()
        ->set_up_super_admin_and_tenant();

    $user = User::factory()->create();
    $site = Site::first();
    $user->sites()->attach($site);

    livewire(UserResource\Pages\EditUser::class, ['record' => $user->id])
        ->assertFormFieldExists('global_roles')
        ->assertFormFieldExists("site_{$site->id}_roles")
        ->assertSuccessful();
});

test('user resource saves global and site roles correctly', function () {
    $this->migrate()
        ->set_up_super_admin_and_tenant();

    $site = Site::first();
    $user = User::factory()->create();
    $user->sites()->attach($site);

    setPermissionsTeamId(null);
    $globalRole = Role::create(['name' => 'admin_'.Str::random(8), 'guard_name' => 'web']);

    setPermissionsTeamId($site->id);
    $siteRole = Role::create(['name' => 'editor_'.Str::random(8), 'guard_name' => 'web']);

    livewire(UserResource\Pages\EditUser::class, ['record' => $user->id])
        ->fillForm([
            'global_roles' => [$globalRole->id],
            "site_{$site->id}_roles" => [$siteRole->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->globalRoles()->where('name', '!=', 'panel_user')->count())->toBe(1);
    expect($user->fresh()->siteRoles($site->id)->count())->toBe(1);
});

test('role assignment persists correctly across multiple sites', function () {
    $this->migrate()
        ->set_up_super_admin_and_tenant();

    $site1 = Site::first();
    $site2 = Site::factory()->create(['name' => 'Site 2', 'domain' => 'site2.test']);
    $user = User::factory()->create();
    $user->sites()->attach([$site1->id, $site2->id]);

    setPermissionsTeamId(null);
    $globalRole = Role::create(['name' => 'admin_'.Str::random(8), 'guard_name' => 'web']);

    setPermissionsTeamId($site1->id);
    $site1Role = Role::create(['name' => 'editor_'.Str::random(8), 'guard_name' => 'web']);

    setPermissionsTeamId($site2->id);
    $site2Role = Role::create(['name' => 'viewer_'.Str::random(8), 'guard_name' => 'web']);

    $user->assignGlobalRole($globalRole);
    $user->assignSiteRole($site1Role, $site1->id);
    $user->assignSiteRole($site2Role, $site2->id);

    expect($user->hasGlobalRole($globalRole->name))->toBeTrue();
    expect($user->hasSiteRole($site1Role->name, $site1->id))->toBeTrue();
    expect($user->hasSiteRole($site2Role->name, $site2->id))->toBeTrue();
    expect($user->hasSiteRole($site1Role->name, $site2->id))->toBeFalse();
});

test('sync roles removes old roles and adds new ones', function () {
    $site = Site::factory()->create();
    $user = User::factory()->create();

    setPermissionsTeamId(null);
    $oldGlobal = Role::create(['name' => 'old_admin_'.Str::random(8), 'guard_name' => 'web']);
    $newGlobal = Role::create(['name' => 'new_admin_'.Str::random(8), 'guard_name' => 'web']);

    setPermissionsTeamId($site->id);
    $oldSite = Role::create(['name' => 'old_editor_'.Str::random(8), 'guard_name' => 'web']);
    $newSite = Role::create(['name' => 'new_editor_'.Str::random(8), 'guard_name' => 'web']);

    $user->assignGlobalRole($oldGlobal);
    $user->assignSiteRole($oldSite, $site->id);

    expect($user->hasGlobalRole($oldGlobal->name))->toBeTrue();
    expect($user->hasSiteRole($oldSite->name, $site->id))->toBeTrue();

    $user->syncGlobalRoles([$newGlobal->id]);
    $user->syncSiteRoles([$newSite->id], $site->id);

    expect($user->hasGlobalRole($oldGlobal->name))->toBeFalse();
    expect($user->hasGlobalRole($newGlobal->name))->toBeTrue();
    expect($user->hasSiteRole($oldSite->name, $site->id))->toBeFalse();
    expect($user->hasSiteRole($newSite->name, $site->id))->toBeTrue();
});
