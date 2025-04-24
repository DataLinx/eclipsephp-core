<?php

use Eclipse\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
});

test('authorized user can access region index', function () {
    Auth::login($this->superAdmin);
    $this->get('/admin/world-regions')->assertOk();
});

test('authorized user can access create region page', function () {
    Auth::login($this->superAdmin);
    $this->get('/admin/world-regions/create')->assertOk();
});

test('region index shows expected structure', function () {
    Auth::login($this->superAdmin);
    $this->get('/admin/world-regions')
        ->assertSee('World Regions') // Adjust based on actual blade content
        ->assertStatus(200);
});

test('can create a geo region and sub-region', function () {
    Auth::login($this->superAdmin);

    $parent = \Eclipse\Core\Models\WorldRegion::factory()->create();
    $childData = [
        'name' => 'West Africa',
        'is_special' => false,
        'parent_id' => $parent->id,
    ];

    $this->post('/admin/world-regions', $childData)
        ->assertRedirect('/admin/world-regions');

    $this->assertDatabaseHas('world_regions', $childData);
});

test('can create a special region', function () {
    Auth::login($this->superAdmin);

    $data = [
        'name' => 'EU',
        'is_special' => true,
    ];

    $this->post('/admin/world-regions', $data)
        ->assertRedirect('/admin/world-regions');

    $this->assertDatabaseHas('world_regions', ['name' => 'EU', 'is_special' => true]);
});