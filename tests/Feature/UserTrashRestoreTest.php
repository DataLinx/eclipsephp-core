<?php

use Eclipse\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('authorized user can trash another user', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create();

    // Simulate admin permission
    Auth::login($admin);
    
    $user->delete();

    expect($user->fresh()->trashed())->toBeTrue();
});

test('user cannot trash himself', function () {
    $user = User::factory()->create();

    Auth::login($user);

    $this->expectException(\Exception::class);
    $user->delete();
});

test('authorized user can restore a trashed user', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create();
    $user->delete(); // Move to trash

    Auth::login($admin);

    $user->restore(); // Restore user

    expect($user->fresh()->trashed())->toBeFalse();
});

test('trashed user cannot login', function () {
    $user = User::factory()->create();
    $user->delete(); // Move to trash

    $attempt = Auth::attempt(['email' => $user->email, 'password' => 'password']);

    expect($attempt)->toBeFalse();
});
