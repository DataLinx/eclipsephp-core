<?php

use Eclipse\Core\Models\User;

test('panel login is visible', function () {
    $this->get('/admin/login')->assertStatus(200);
});

test('unauthorized access can be prevented', function () {
    $this->get('/admin')
        ->assertRedirect('admin/login');
});

test('telescope is not visible', function () {
    $this->get('/telescope')->assertStatus(404);
});

test('horizon is not accessible for guests', function () {
    $this->get('/horizon')->assertStatus(403);
});

test('horizon is accessible for allowed users', function () {
    // Create users
    $user = User::factory()->create();
    $other_user = User::factory()->create();

    // Assert they don't have permission
    $this->assertFalse($user->can('viewHorizon'));
    $this->assertFalse($other_user->can('viewHorizon'));

    // Set the first user as allowed
    Config::set('eclipse.horizon.emails', [$user->email]);

    // Assert the first user now has permission
    $this->assertTrue($user->can('viewHorizon'));

    // Test access
    $this->actingAs($user);
    $this->get('/horizon')->assertStatus(200);

    $this->actingAs($other_user);
    $this->get('/horizon')->assertStatus(403);
});

test('log viewer is not accessible for guests', function () {
    $this->get(config('log-viewer.route_path', 'log-viewer'))->assertStatus(403);
});

test('log viewer is not accessible for non-super-admin users', function () {
    // Create a regular user
    $user = User::factory()->create();

    // Assert the user doesn't have super_admin role
    $this->assertFalse($user->hasRole('super_admin'));

    // Test access
    $this->actingAs($user);
    $this->get(config('log-viewer.route_path', 'log-viewer'))->assertStatus(403);
});

test('log viewer is accessible for super admin users', function () {
    // Create a user
    $user = User::factory()->create();

    // Assign super_admin role
    $user->assignRole('super_admin');

    // Assert the user has super_admin role
    $this->assertTrue($user->hasRole('super_admin'));

    // Test access
    $this->actingAs($user);
    $this->get(config('log-viewer.route_path', 'log-viewer'))->assertStatus(200);
});
