<?php

use Eclipse\Core\Filament\Resources\UserResource;
use Eclipse\Core\Filament\Resources\UserResource\Pages\CreateUser;
use Eclipse\Core\Filament\Resources\UserResource\Pages\EditUser;
use Eclipse\Core\Filament\Resources\UserResource\Pages\ListUsers;
use Eclipse\Core\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Hash;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
    UserResource::canViewAny();
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

    livewire(EditUser::class, ['record' => $user->id])
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
    $user = User::factory()->create();

    livewire(ListUsers::class)
        ->assertSuccessful()
        ->assertTableActionExists(DeleteAction::class)
        ->assertTableActionEnabled(DeleteAction::class, $user)
        ->callTableAction(DeleteAction::class, $user);

    $this->assertSoftDeleted('users', ['id' => $user->id]);
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
