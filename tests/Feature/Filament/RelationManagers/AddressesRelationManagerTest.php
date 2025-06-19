<?php

use Eclipse\Core\Filament\Resources\UserResource\Pages\EditUser;
use Eclipse\Core\Filament\Resources\UserResource\Pages\ViewUser;
use Eclipse\Core\Filament\Resources\UserResource\RelationManagers\AddressesRelationManager;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
});

it('can render addresses relation manager on edit user page', function (): void {
    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $this->superAdmin,
        'pageClass' => EditUser::class,
    ])->assertSuccessful();
});

it('can render addresses relation manager on view user page', function (): void {
    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $this->superAdmin,
        'pageClass' => ViewUser::class,
    ])->assertSuccessful();
});
