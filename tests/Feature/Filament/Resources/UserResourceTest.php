<?php

use Eclipse\Core\Filament\Resources\UserResource;
use Eclipse\Core\Filament\Resources\UserResource\Pages\CreateUser;
use Eclipse\Core\Filament\Resources\UserResource\Pages\ListUsers;
use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Eclipse\Core\Models\User\Role;
use Filament\Facades\Filament;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Hash;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
});

test('authorized access can be allowed', function () {
    $this->get(UserResource::getUrl())
        ->assertOk();
});

test('create user screen can be rendered', function () {
    $this->get(UserResource::getUrl('create'))
        ->assertOk();
});

test('user form validation works', function () {
    $component = livewire(CreateUser::class);

    $component->assertFormExists();

    // Test required fields
    $component->call('create')
        ->assertHasFormErrors([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);

    // Test with valid data
    $component->fillForm([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@doe.com',
        'password' => 'password',
    ])->call('create')
        ->assertHasNoFormErrors();
});

test('new user can be created', function () {
    $data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@doe.net',
        'password' => 'johndoe',
    ];

    livewire(CreateUser::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'john@doe.net')->first();
    expect($user)->toBeObject();

    foreach ($data as $key => $val) {
        if ($key === 'password') {
            expect(Hash::check($val, $user->password))->toBeTrue('Hashed password differs from plain-text!');
        } else {
            expect($user->$key)->toEqual($val);
        }
    }
});

test('edit user screen can be rendered', function () {
    $user = User::factory()->create();

    $this->get(UserResource::getUrl('edit', ['record' => $user]))
        ->assertOk();
});

test('existing user can be updated', function () {
    $user = User::factory()->create();

    $data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'updated@example.com',
        // Without password, since it's not required
    ];

    livewire(\Eclipse\Core\Filament\Resources\UserResource\Pages\EditUser::class, ['record' => $user->id])
        ->fillForm($data)
        ->call('save')
        ->assertHasNoFormErrors();

    $user = $user->fresh();

    foreach ($data as $key => $val) {
        expect($user->$key)->toEqual($val);
    }
});

test('users table page can be rendered', function () {
    livewire(ListUsers::class)->assertSuccessful();
});

test('users can be searched', function () {
    // Create 5 users
    User::factory()->count(5)->create();

    // Get first user
    $user = User::first();

    // Get second user
    $user2 = User::skip(1)->first();

    livewire(ListUsers::class)
        ->searchTable($user->name)
        ->assertSee($user->name)
        ->assertDontSee($user2->name);
});

test('user can be deleted', function () {
    $site = Site::factory()->create();

    $user = User::factory()->create();
    $user->sites()->attach($site);

    Filament::setTenant($site);

    livewire(ListUsers::class, ['tenant' => $site])
        ->assertSuccessful()
        ->assertTableActionExists(DeleteAction::class)
        ->assertTableActionEnabled(DeleteAction::class, $user)
        ->callTableAction(DeleteAction::class, $user);

    $user->refresh();
    expect($user->trashed())->toBeTrue();
});

test('authed user cannot delete himself', function () {
    $superAdmin = User::withTrashed()->find($this->superAdmin->id);

    // Assert on table row action
    livewire(ListUsers::class)
        ->assertTableActionDisabled(DeleteAction::class, $superAdmin);

    // Assert on bulk delete
    $users = User::all();

    livewire(ListUsers::class)
        ->callTableBulkAction(DeleteBulkAction::class, $users)
        ->assertNotified('Error');

    foreach ($users as $user) {
        $this->assertModelExists($user);
    }
});

test('user list shows only current site users by default', function () {
    $site1 = Site::factory()->create();
    $site2 = Site::factory()->create();

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $user1->sites()->attach($site1);
    $user2->sites()->attach($site2);
    $user3->sites()->attach([$site1, $site2]);

    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin);
    Filament::setTenant($site1);

    livewire(ListUsers::class, ['tenant' => $site1])
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$user1, $user3])
        ->assertCanNotSeeTableRecords([$user2]);
});

test('user list shows global and site role columns', function () {
    $site = Site::factory()->create();
    $user = User::factory()->create();

    $globalRole = Role::create([
        'name' => 'global_admin',
        'guard_name' => 'web',
        config('permission.column_names.team_foreign_key') => null, // Global role
    ]);

    $siteRole = Role::create([
        'name' => 'site_editor',
        'guard_name' => 'web',
        config('permission.column_names.team_foreign_key') => $site->id, // Site-specific role
    ]);

    $user->sites()->attach($site);
    $user->assignRole($globalRole);
    $user->assignRole($siteRole);

    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin);
    Filament::setTenant($site);

    livewire(ListUsers::class, ['tenant' => $site])
        ->assertSuccessful()
        ->assertTableColumnExists('global_roles')
        ->assertTableColumnExists('site_roles');
});

test('filter shows users from all accessible sites when enabled', function () {
    $site1 = Site::factory()->create();
    $site2 = Site::factory()->create();

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1->sites()->attach($site1);
    $user2->sites()->attach($site2);

    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $admin->sites()->attach([$site1, $site2]);
    $this->actingAs($admin);
    Filament::setTenant($site1);

    livewire(ListUsers::class, ['tenant' => $site1])
        ->filterTable('user_visibility', true)
        ->assertCanSeeTableRecords([$user1, $user2]);
});

test('role filters work for global and site roles', function () {
    $site = Site::factory()->create();

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1->sites()->attach($site);
    $user2->sites()->attach($site);

    $globalRole = Role::create([
        'name' => 'global_admin',
        config('permission.column_names.team_foreign_key') => null, // Global role
    ]);

    $siteRole = Role::create([
        'name' => 'site_editor',
        config('permission.column_names.team_foreign_key') => $site->id, // Site-specific role
    ]);

    $user1->assignRole($globalRole);
    $user2->assignRole($siteRole);

    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $admin->sites()->attach($site);

    $this->actingAs($admin);
    Filament::setTenant($site);

    livewire(ListUsers::class, ['tenant' => $site])
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$user1, $user2]);
});
