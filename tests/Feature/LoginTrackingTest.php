<?php

use Eclipse\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('new users have no login history', function () {
    $user = User::factory()->create();

    expect($user->last_login_at)->toBeNull();
    expect($user->login_count)->toBe(0);
});

test('user login updates last login timestamp and increments count', function () {
    $user = User::factory()->create([
        'last_login_at' => null,
        'login_count' => 0,
    ]);

    // Simulate login
    Auth::login($user);
    $user->refresh(); // Reload from DB to reflect changes

    expect($user->last_login_at)->not->toBeNull();
    expect($user->login_count)->toBe(1);
});

test('guest users do not have login tracking data', function () {
    $this->get('/admin')->assertRedirect('admin/login');

    expect(Auth::user())->toBeNull();
});
