<?php

use Eclipse\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
    $this->admin = auth()->user(); 

    Permission::firstOrCreate(['name' => 'delete users']);
    Permission::firstOrCreate(['name' => 'restore users']);

    $this->admin->syncPermissions(['delete users', 'restore users']);
});

test('authorized user with permission can trash another user', function () {
    $user = User::factory()->create();

    Auth::login($this->admin);

    expect($this->admin->can('delete users'))->toBeTrue();

    $user->delete();

    expect($user->fresh()->trashed())->toBeTrue();
});

test('non-authorized user cannot trash another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Auth::login($user);

    expect($user->can('delete users'))->toBeFalse();

    $this->expectException(UnauthorizedException::class);
    
    $otherUser->delete();
});

test('user cannot trash himself', function () {
    Auth::login($this->admin);

    $this->expectException(\Exception::class);

    $this->admin->delete();
});

test('authorized user with permission can restore a trashed user', function () {
    $user = User::factory()->create();
    $user->delete();

    Auth::login($this->admin);

    expect($this->admin->can('restore users'))->toBeTrue();

    $user->restore();

    expect($user->fresh()->trashed())->toBeFalse();
});

test('non-authorized user cannot restore another user', function () {
    $userToTrash = User::factory()->create();
    $userToTrash->delete();

    $nonAuthorizedUser = User::factory()->create();

    Auth::login($nonAuthorizedUser);

    expect($nonAuthorizedUser->can('restore users'))->toBeFalse();

    $this->expectException(UnauthorizedException::class);

    $userToTrash->restore();
});

test('trashed user cannot login', function () {
    $userToTrash = User::factory()->create();
    $userToTrash->delete(); 
    
    Auth::logout();

    $attempt = Auth::attempt(['email' => $userToTrash->email, 'password' => 'password']);

    expect($attempt)->toBeFalse();
});