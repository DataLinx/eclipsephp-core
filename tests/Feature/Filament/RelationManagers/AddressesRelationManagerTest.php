<?php

use Eclipse\Core\Enums\AddressType;
use Eclipse\Core\Filament\Resources\UserResource\Pages\EditUser;
use Eclipse\Core\Filament\Resources\UserResource\RelationManagers\AddressesRelationManager;
use Eclipse\Core\Models\User\Address;
use Filament\Forms\Components\Repeater;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
    $this->undoRepeaterFake = Repeater::fake();
});

afterEach(function () {
    ($this->undoRepeaterFake)();
});

function prepareFactoryDataForForm(): array
{
    $data = Address::factory()->definition();
    unset($data['user_id']);

    $data['street_address'] = collect($data['street_address'])
        ->map(fn ($address) => ['street_address' => $address])
        ->toArray();

    return $data;
}

it('can render address relation manager', function (): void {
    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $this->superAdmin,
        'pageClass' => EditUser::class,
    ])->assertSuccessful();
});

it('can create address', function (): void {
    $data = prepareFactoryDataForForm();

    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $this->superAdmin,
        'pageClass' => EditUser::class,
    ])
        ->callTableAction('create', data: $data)
        ->assertHasNoTableActionErrors();

    expect($this->superAdmin->addresses()->count())->toBe(1);
});

it('can edit address', function (): void {
    $address = Address::factory()->for($this->superAdmin)->create();

    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $this->superAdmin,
        'pageClass' => EditUser::class,
    ])
        ->callTableAction('edit', $address, data: ['recipient' => 'Updated Name'])
        ->assertHasNoTableActionErrors();

    expect($address->fresh()->recipient)->toBe('Updated Name');
});

it('can delete address', function (): void {
    $address = Address::factory()->for($this->superAdmin)->create();

    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $this->superAdmin,
        'pageClass' => EditUser::class,
    ])
        ->callTableAction('delete', $address)
        ->assertHasNoTableActionErrors();

    expect($this->superAdmin->fresh()->addresses)->toHaveCount(0);
});

it('can view address', function (): void {
    $address = Address::factory()->for($this->superAdmin)->create();

    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $this->superAdmin,
        'pageClass' => EditUser::class,
    ])
        ->callTableAction('view', $address)
        ->assertHasNoTableActionErrors();
});

it('can bulk delete addresses', function (): void {
    $addresses = Address::factory()->count(3)->for($this->superAdmin)->create();

    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $this->superAdmin,
        'pageClass' => EditUser::class,
    ])
        ->callTableBulkAction('delete', $addresses)
        ->assertHasNoTableBulkActionErrors();

    expect($this->superAdmin->fresh()->addresses)->toHaveCount(0);
});

it('can soft delete and restore address', function (): void {
    $address = Address::factory()->for($this->superAdmin)->create();

    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $this->superAdmin,
        'pageClass' => EditUser::class,
    ])
        ->callTableBulkAction('delete', [$address])
        ->assertHasNoTableBulkActionErrors();

    expect($address->fresh()->trashed())->toBeTrue();
    expect($this->superAdmin->addresses()->count())->toBe(0); // Active count
    expect($this->superAdmin->addresses()->withTrashed()->count())->toBe(1); // Total count

    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $this->superAdmin,
        'pageClass' => EditUser::class,
    ])
        ->filterTable('trashed', 'with')
        ->callTableBulkAction('restore', [$address])
        ->assertHasNoTableBulkActionErrors();

    expect($address->fresh()->trashed())->toBeFalse();
    expect($this->superAdmin->addresses()->count())->toBe(1);
});

it('can force delete address', function (): void {
    $address = Address::factory()->for($this->superAdmin)->create();

    $address->delete();

    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $this->superAdmin,
        'pageClass' => EditUser::class,
    ])
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('forceDelete', [$address])
        ->assertHasNoTableBulkActionErrors();

    expect(Address::withTrashed()->find($address->id))->toBeNull();
});

it('can filter addresses by type', function (): void {
    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $this->superAdmin,
        'pageClass' => EditUser::class,
    ])
        ->filterTable('type', AddressType::DEFAULT_ADDRESS->value)
        ->assertSuccessful();
});
