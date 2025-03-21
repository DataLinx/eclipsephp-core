<?php

use Eclipse\Core\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
});

test('authorized user with permission can trash another user', function () {
    $user = User::factory()->create();
    Auth::login($this->superAdmin);
    $this->assertTrue($this->superAdmin->hasPermissionTo('delete_user'));
    $this->assertTrue($this->superAdmin->can('delete', $user));
    $user->delete();
    $this->assertTrue($user->fresh()->trashed());
});

test('non-authorized user cannot trash another user', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();
    Auth::login($user);
    $this->assertFalse($user->hasPermissionTo('delete_user'));
    $this->assertFalse($user->can('delete', $targetUser));
    $this->expectException(AuthorizationException::class);
    Gate::authorize('delete', $targetUser);
});

test('user cannot trash himself', function () {
    Auth::login($this->superAdmin);
    $this->assertFalse($this->superAdmin->can('delete', $this->superAdmin));
    try {
        Gate::authorize('delete', $this->superAdmin);
        $this->fail('User was able to authorize self-deletion, which should not be allowed');
    } catch (AuthorizationException $e) {
        $this->assertTrue(true);
    }
    $this->assertFalse($this->superAdmin->fresh()->trashed());
});

test('authorized user with restore permission can restore a trashed user', function () {
    $user = User::factory()->create();
    $user->delete();
    Auth::login($this->superAdmin);
    $this->assertTrue($this->superAdmin->hasPermissionTo('restore_user'));
    $this->assertTrue($this->superAdmin->can('restore', $user));
    $user->restore();
    $this->assertFalse($user->fresh()->trashed());
});

test('authorized user with restore_any permission can restore any trashed user', function () {
    $userToTrash = User::factory()->create();
    $userToTrash->delete();
    $limitedAdmin = User::factory()->create();
    $limitedAdmin->givePermissionTo('restore_any_user');
    Auth::login($limitedAdmin);
    $this->assertTrue($limitedAdmin->hasPermissionTo('restore_any_user'));
    $this->assertTrue($limitedAdmin->can('restoreAny', User::class));
    $userToTrash->restore();
    $this->assertFalse($userToTrash->fresh()->trashed());
});

test('non-authorized user cannot restore another user', function () {
    $userToTrash = User::factory()->create();
    $userToTrash->delete();
    $nonAuthorizedUser = User::factory()->create();
    Auth::login($nonAuthorizedUser);
    $this->assertFalse($nonAuthorizedUser->hasPermissionTo('restore_user'));
    $this->assertFalse($nonAuthorizedUser->can('restore', $userToTrash));
    $this->expectException(AuthorizationException::class);
    Gate::authorize('restore', $userToTrash);
});

test('trashed user cannot login', function () {
    $userToTrash = User::factory()->create([
        'email' => 'trashed@example.com',
        'password' => bcrypt('password'),
    ]);
    $userToTrash->delete();
    Auth::logout();
    $attempt = Auth::attempt([
        'email' => 'trashed@example.com',
        'password' => 'password',
    ]);
    $this->assertFalse($attempt);
});

test('authorized user with permission can force delete a trashed user', function () {
    $user = User::factory()->create();
    $user->delete();
    Auth::login($this->superAdmin);
    $this->assertTrue($this->superAdmin->hasPermissionTo('force_delete_user'));
    $this->assertTrue($this->superAdmin->can('forceDelete', $user));
    $user->forceDelete();
    $this->assertNull(User::withTrashed()->find($user->id));
});

test('non-authorized user cannot force delete a trashed user', function () {
    $userToTrash = User::factory()->create();
    $userToTrash->delete();
    $nonAuthorizedUser = User::factory()->create();
    Auth::login($nonAuthorizedUser);
    $this->assertFalse($nonAuthorizedUser->hasPermissionTo('force_delete_user'));
    $this->assertFalse($nonAuthorizedUser->can('forceDelete', $userToTrash));
    $this->expectException(AuthorizationException::class);
    Gate::authorize('forceDelete', $userToTrash);
});

test('can view trashed users when user has permissions', function () {
    $trashedUser = User::factory()->create();
    $trashedUser->delete();
    Auth::login($this->superAdmin);
    $this->assertTrue($this->superAdmin->hasPermissionTo('view_any_user'));
    $this->assertTrue($this->superAdmin->hasPermissionTo('view_user'));
    $this->assertTrue($this->superAdmin->can('viewAny', User::class));
    $this->assertTrue($this->superAdmin->can('view', $trashedUser));
});

test('filament resource can handle trashed users', function () {
    $userToTrash = User::factory()->create([
        'name' => 'Trashed User',
        'email' => 'trashed@example.com',
    ]);
    $userToTrash->delete();
    Auth::login($this->superAdmin);
    $this->assertTrue($this->superAdmin->can('viewAny', User::class));
    $this->assertNotNull(User::withTrashed()->where('email', 'trashed@example.com')->first());
    $this->assertTrue(User::withTrashed()->where('email', 'trashed@example.com')->first()->trashed());
});
