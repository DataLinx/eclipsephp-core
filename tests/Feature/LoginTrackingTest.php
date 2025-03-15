<?php

use Eclipse\Core\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('new users have no login history', function () {
    $user = User::factory()->create();

    expect($user->last_login_at)->toBeNull();
    expect($user->login_count ?? 0)->toBe(0);
});

test('user login updates last login timestamp and increments count', function () {
    $user = User::factory()->create([
        'last_login_at' => null,
        'login_count' => 0,
    ]);

    // Simulate login
    Auth::login($user);
    $user->updateLoginTracking();
    $user->refresh();

    expect($user->last_login_at)->not->toBeNull();
    expect($user->login_count)->toBe(1);
});

test('multiple logins correctly increment login count', function () {
    $user = User::factory()->create([
        'login_count' => 2, // User has logged in twice before
    ]);

    Auth::login($user);
    $user->updateLoginTracking();
    $user->refresh();

    expect($user->login_count)->toBe(3); // Should increase by 1
});

test('login tracking does not reset on logout', function () {
    $user = User::factory()->create([
        'last_login_at' => now()->subDays(1),
        'login_count' => 5,
    ]);

    Auth::login($user);
    $user->updateLoginTracking();
    Auth::logout();
    $user->refresh();

    expect($user->last_login_at)->not->toBeNull();
    expect($user->login_count)->toBe(6); // Login count should remain after logout
});

test('guest users do not have login tracking data', function () {
    $this->get('/admin')->assertRedirect('admin/login');

    expect(Auth::user())->toBeNull();
});
