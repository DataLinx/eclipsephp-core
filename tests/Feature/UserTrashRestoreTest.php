<?php

use Eclipse\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
    $this->admin = auth()->user(); 

    $shieldPermissions = [
        'view_any_user',
        'view_user',
        'create_user',
        'update_user',
        'delete_user',
        'delete_any_user',
        'restore_user',
        'restore_any_user',
        'force_delete_user',
        'force_delete_any_user',
    ];
    
    foreach ($shieldPermissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    // Assign all permissions to admin for testing
    $this->admin->syncPermissions($shieldPermissions);
});

test('authorized user with permission can trash another user', function () {
    $user = User::factory()->create();

    Auth::login($this->admin);

    // Verify the user has the Shield-generated permission
    $this->assertTrue($this->admin->hasPermissionTo('delete_user'));
    
    // Verify permission check through policy
    $this->assertTrue($this->admin->can('delete', $user)); 

    $user->delete();

    $this->assertTrue($user->fresh()->trashed());
});

test('non-authorized user cannot trash another user', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();
    
    // User without Shield permissions
    Auth::login($user);
    
    // Verify user doesn't have the Shield-generated permission
    $this->assertFalse($user->hasPermissionTo('delete_user'));
    
    // Verify policy check fails
    $this->assertFalse($user->can('delete', $targetUser));
    
    $this->expectException(AuthorizationException::class);
    Gate::authorize('delete', $targetUser);
});

test('user cannot trash himself', function () {
    Auth::login($this->admin);
    
    // Even with permissions, admin shouldn't be able to delete themselves
    $this->assertFalse($this->admin->can('delete', $this->admin));
    
    // Try to delete and expect an exception
    try {
        // Attempt to authorize the action (should fail)
        Gate::authorize('delete', $this->admin);
        $this->fail('User was able to authorize self-deletion, which should not be allowed');
    } catch (AuthorizationException $e) {
        // Expected behavior
        $this->assertTrue(true);
    }
    
    // Verify admin still exists and is not trashed
    $this->assertFalse($this->admin->fresh()->trashed());
});

test('authorized user with restore permission can restore a trashed user', function () {
    $user = User::factory()->create();
    $user->delete(); // Trashing the user

    Auth::login($this->admin);

    // Verify the user has the Shield-generated permission
    $this->assertTrue($this->admin->hasPermissionTo('restore_user'));
    
    // Check policy integration
    $this->assertTrue($this->admin->can('restore', $user));

    // Restore the trashed user
    $user->restore();

    // Assert the user is no longer trashed
    $this->assertFalse($user->fresh()->trashed());
});

test('authorized user with restore_any permission can restore any trashed user', function () {
    // Create and trash a regular user
    $userToTrash = User::factory()->create();
    $userToTrash->delete(); // Trash the user
    
    // Create another admin with restore_any permission only
    $limitedAdmin = User::factory()->create();
    
    // Give this admin only the restore_any permission using Shield convention
    $limitedAdmin->givePermissionTo('restore_any_user');
    
    Auth::login($limitedAdmin);
    
    // First check - directly checking Shield permission
    $this->assertTrue($limitedAdmin->hasPermissionTo('restore_any_user'));
    
    // Second check - checking policy gate (should use Shield's generated policy)
    $this->assertTrue($limitedAdmin->can('restoreAny', User::class));
    
    // Now restore the user
    $userToTrash->restore();
    
    // Check the user is no longer trashed
    $this->assertFalse($userToTrash->fresh()->trashed());
});

test('non-authorized user cannot restore another user', function () {
    $userToTrash = User::factory()->create();
    $userToTrash->delete(); // Trashing the user

    $nonAuthorizedUser = User::factory()->create();

    Auth::login($nonAuthorizedUser);

    // Verify user doesn't have Shield permission
    $this->assertFalse($nonAuthorizedUser->hasPermissionTo('restore_user'));
    
    // Verify policy check fails
    $this->assertFalse($nonAuthorizedUser->can('restore', $userToTrash));
    
    $this->expectException(AuthorizationException::class);
    Gate::authorize('restore', $userToTrash);
});

test('trashed user cannot login', function () {
    $userToTrash = User::factory()->create([
        'email' => 'trashed@example.com',
        'password' => bcrypt('password')
    ]);
    $userToTrash->delete(); // Trashing the user
    
    Auth::logout();

    // Attempt to log in with the trashed user's credentials
    $attempt = Auth::attempt([
        'email' => 'trashed@example.com', 
        'password' => 'password'
    ]);

    // Assert that login attempt fails for trashed user
    $this->assertFalse($attempt);
});

test('authorized user with permission can force delete a trashed user', function () {
    $user = User::factory()->create();
    $user->delete(); // Trash first
    
    Auth::login($this->admin);
    
    // Verify the user has the Shield-generated permission
    $this->assertTrue($this->admin->hasPermissionTo('force_delete_user'));
    
    // Check policy integration
    $this->assertTrue($this->admin->can('forceDelete', $user));
    
    $user->forceDelete();
    
    $this->assertNull(User::withTrashed()->find($user->id));
});

test('non-authorized user cannot force delete a trashed user', function () {
    $userToTrash = User::factory()->create();
    $userToTrash->delete(); // Trash first
    
    $nonAuthorizedUser = User::factory()->create();
    Auth::login($nonAuthorizedUser);
    
    // Verify user doesn't have Shield permission
    $this->assertFalse($nonAuthorizedUser->hasPermissionTo('force_delete_user'));
    
    // Verify policy check fails
    $this->assertFalse($nonAuthorizedUser->can('forceDelete', $userToTrash));
    
    $this->expectException(AuthorizationException::class);
    Gate::authorize('forceDelete', $userToTrash);
});

test('can view trashed users when user has permissions', function () {
    $trashedUser = User::factory()->create();
    $trashedUser->delete();
    
    Auth::login($this->admin);

    // Verify the user has the Shield-generated permissions
    $this->assertTrue($this->admin->hasPermissionTo('view_any_user'));
    $this->assertTrue($this->admin->hasPermissionTo('view_user'));
    
    // Check policy integration
    $this->assertTrue($this->admin->can('viewAny', User::class));
    $this->assertTrue($this->admin->can('view', $trashedUser));
});

test('filament resource can handle trashed users', function () {    
    // Create and trash a user
    $userToTrash = User::factory()->create([
        'name' => 'Trashed User',
        'email' => 'trashed@example.com'
    ]);
    $userToTrash->delete();
    
    Auth::login($this->admin);
    
    // Verify admin can see trashed users
    $this->assertTrue($this->admin->can('viewAny', User::class));
    
    $this->assertNotNull(User::withTrashed()->where('email', 'trashed@example.com')->first());
    $this->assertTrue(User::withTrashed()->where('email', 'trashed@example.com')->first()->trashed());
});