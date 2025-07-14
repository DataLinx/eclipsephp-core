<?php

use Eclipse\Core\Enums\AddressType;
use Eclipse\Core\Filament\Resources\UserResource\Pages\EditUser;
use Eclipse\Core\Filament\Resources\UserResource\RelationManagers\AddressesRelationManager;
use Eclipse\Core\Models\User;
use Eclipse\Core\Models\User\Address;
use Filament\Forms\Components\Repeater;

use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertContains;

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

it('each user can edit only his own addresses', function (): void {
    $otherUser = User::factory()->create();
    $otherUserAddress = Address::factory()->for($otherUser)->create();

    $userAddress = Address::factory()->for($this->superAdmin)->create();

    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $this->superAdmin,
        'pageClass' => EditUser::class,
    ])
        ->assertCountTableRecords(1)
        ->assertSeeText($userAddress->recipient)
        ->assertDontSeeText($otherUserAddress->recipient);

    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $otherUser,
        'pageClass' => EditUser::class,
    ])
        ->assertCountTableRecords(1)
        ->assertSeeText($otherUserAddress->recipient)
        ->assertDontSeeText($userAddress->recipient);
});

it('admins with user update permission can edit addresses for any user', function (): void {
    $regularUser = User::factory()->create();
    $regularUserAddress = Address::factory()->for($regularUser)->create();

    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $regularUser,
        'pageClass' => EditUser::class,
    ])
        ->callTableAction('edit', $regularUserAddress, data: ['recipient' => 'Admin Updated'])
        ->assertHasNoTableActionErrors();

    expect($regularUserAddress->fresh()->recipient)->toBe('Admin Updated');
});

it('only one address can be default - new default unsets old one', function (): void {
    $firstAddress = Address::factory()->for($this->superAdmin)->create([
        'type' => [AddressType::DEFAULT_ADDRESS->value],
    ]);

    $secondAddress = Address::factory()->for($this->superAdmin)->create([
        'type' => [AddressType::COMPANY_ADDRESS->value],
    ]);

    $secondAddress->type = [AddressType::DEFAULT_ADDRESS->value];
    $secondAddress->save();

    $firstRefreshed = $firstAddress->fresh();
    $secondRefreshed = $secondAddress->fresh();

    $hasDefault = in_array(AddressType::DEFAULT_ADDRESS->value, $secondRefreshed->type ?? []);

    expect($hasDefault)->toBeTrue('Manual check should pass');

    expect($secondRefreshed->type)->toContain('default_address');

    assertContains(AddressType::DEFAULT_ADDRESS->value, $secondRefreshed->type, 'PHPUnit assertion should work');

    expect($firstRefreshed->type)->not->toContain(AddressType::DEFAULT_ADDRESS->value);

    $defaultCount = $this->superAdmin->addresses()->get()->filter(function ($address) {
        return in_array(AddressType::DEFAULT_ADDRESS->value, $address->type ?? []);
    })->count();

    expect($defaultCount)->toBe(1, 'Should have exactly one default address');
});

it('when deleting default address the oldest becomes default', function (): void {
    $oldestAddress = Address::factory()->for($this->superAdmin)->create([
        'type' => [AddressType::COMPANY_ADDRESS->value],
        'created_at' => now()->subDays(5),
    ]);

    $defaultAddress = Address::factory()->for($this->superAdmin)->create([
        'type' => [AddressType::DEFAULT_ADDRESS->value],
        'created_at' => now(),
    ]);

    livewire(AddressesRelationManager::class, [
        'ownerRecord' => $this->superAdmin,
        'pageClass' => EditUser::class,
    ])
        ->callTableAction('delete', $defaultAddress)
        ->assertHasNoTableActionErrors();

    expect($oldestAddress->fresh()->type)->toContain(AddressType::DEFAULT_ADDRESS->value);
});
