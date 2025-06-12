<?php

use Eclipse\Core\Filament\Resources\RoleResource\Pages\CreateRole;
use Eclipse\Core\Filament\Resources\RoleResource\Pages\EditRole;
use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Eclipse\Core\Models\User\Role;
use Filament\Facades\Filament;
use function Pest\Livewire\livewire;

test('role can be created with site assignment', function () {
    $site = Site::factory()->create();
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    
    $this->actingAs($admin);
    Filament::setTenant($site);
    
    livewire(CreateRole::class, ['tenant' => $site])
        ->fillForm([
            'name' => 'Site Manager',
            'site_id' => $site->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('roles', [
        'name' => 'Site Manager',
        'site_id' => $site->id,
    ]);
});

test('role can be edited to change site assignment', function () {
    $site1 = Site::factory()->create();
    $site2 = Site::factory()->create();
    $role = Role::factory()->create(['site_id' => $site1->id]);
    
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    
    $this->actingAs($admin);
    Filament::setTenant($site1);
    
    livewire(EditRole::class, ['record' => $role->id, 'tenant' => $site1])
        ->fillForm([
            'site_id' => $site2->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'site_id' => $site2->id,
    ]);
});