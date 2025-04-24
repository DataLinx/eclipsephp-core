<?php

use Eclipse\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
});

test('authorized user can access country index', function () {
    Auth::login($this->superAdmin);
    $this->get('/admin/countries')->assertOk();
});

test('authorized user can access create country page', function () {
    Auth::login($this->superAdmin);
    $this->get('/admin/countries/create')->assertOk();
});

test('country index shows expected structure', function () {
    Auth::login($this->superAdmin);
    $this->get('/admin/countries')
        ->assertSee('Countries') // Adjust based on actual blade titles
        ->assertStatus(200);
});
