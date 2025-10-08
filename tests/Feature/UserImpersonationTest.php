<?php

use Eclipse\Core\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use STS\FilamentImpersonate\Actions\Impersonate as ImpersonateAction;

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();

    // Create a target user to impersonate
    $this->targetUser = User::factory()->create([
        'first_name' => 'Target',
        'last_name' => 'User',
        'email' => 'target@example.com',
    ]);

    // Create a user with impersonate permission
    $this->authorizedUser = User::factory()->create([
        'first_name' => 'Authorized',
        'last_name' => 'User',
        'email' => 'authorized@example.com',
    ]);
    $this->authorizedUser->givePermissionTo('impersonate_user');

    // Create a user without impersonate permission
    $this->unauthorizedUser = User::factory()->create([
        'first_name' => 'Unauthorized',
        'last_name' => 'User',
        'email' => 'unauthorized@example.com',
    ]);
});

test('non-authorized user cannot impersonate other users', function () {
    // Login as unauthorized user
    Auth::login($this->unauthorizedUser);

    // Assert user doesn't have impersonate permission
    $this->assertFalse($this->unauthorizedUser->hasPermissionTo('impersonate_user'));
    $this->assertFalse($this->unauthorizedUser->can('impersonate', User::class));
    $this->assertFalse($this->unauthorizedUser->canImpersonate());

    // Test authorization gate
    $this->expectException(AuthorizationException::class);
    Gate::authorize('impersonate', User::class);
});

test('non-authorized user cannot see and trigger the impersonate table and page action', function () {
    // Login as unauthorized user
    Auth::login($this->unauthorizedUser);

    // Assert user doesn't have impersonate permission
    $this->assertFalse($this->unauthorizedUser->hasPermissionTo('impersonate_user'));

    // Create an instance of the Impersonate action
    $action = ImpersonateAction::make('impersonate')
        ->record($this->targetUser);

    // Assert the action is not authorized
    $this->assertFalse($action->isVisible());
    $this->assertFalse($action->isEnabled());

    // Create an instance of the Impersonate page action
    $action = ImpersonateAction::make('impersonate')
        ->record($this->targetUser);

    // Assert the action is not authorized
    $this->assertFalse($action->isVisible());
    $this->assertFalse($action->isEnabled());
});

test('authorized user can impersonate other users', function () {
    // Login as authorized user
    Auth::login($this->authorizedUser);

    // Assert user has impersonate permission
    $this->assertTrue($this->authorizedUser->hasPermissionTo('impersonate_user'));
    $this->assertTrue($this->authorizedUser->can('impersonate', User::class));
    $this->assertTrue($this->authorizedUser->canImpersonate());

    // Test authorization gate
    $this->assertTrue(Gate::allows('impersonate', User::class));
});

test('authorized user can see and trigger the impersonate table and page action', function () {
    // Login as authorized user
    Auth::login($this->authorizedUser);

    // Assert user has impersonate permission
    $this->assertTrue($this->authorizedUser->hasPermissionTo('impersonate_user'));

    // Create an instance of the Impersonate action
    $action = ImpersonateAction::make('impersonate')
        ->record($this->targetUser);

    // Assert the action is authorized
    $this->assertTrue($action->isVisible());
    $this->assertTrue($action->isEnabled());

    // Create an instance of the Impersonate page action
    $action = ImpersonateAction::make('impersonate')
        ->record($this->targetUser);

    // Assert the action is authorized
    $this->assertTrue($action->isVisible());
    $this->assertTrue($action->isEnabled());
});
