<?php

use Eclipse\Core\Filament\Resources\LocaleResource;
use Eclipse\Core\Filament\Resources\LocaleResource\Pages\ListLocales;
use Eclipse\Core\Models\Locale;
use Illuminate\Support\Arr;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
    LocaleResource::canViewAny();
});

test('unauthorized access can be prevented', function () {
    // Create regular user with no permissions
    $this->set_up_common_user_and_tenant();

    // Create test locale
    $locale = Locale::factory()->create();

    // View table
    $this->get(LocaleResource::getUrl())
        ->assertForbidden();

    // Add direct permission to view the table, since otherwise any other action below is not available even for testing
    $this->user->givePermissionTo('view_any_locale');

    // Create locale
    livewire(ListLocales::class)
        ->assertActionDisabled('create');

    // Edit locale
    livewire(ListLocales::class)
        ->assertCanSeeTableRecords([$locale])
        ->assertTableActionDisabled('edit', $locale);

    // Delete locale
    livewire(ListLocales::class)
        ->assertTableActionDisabled('delete', $locale)
        ->assertTableBulkActionDisabled('delete');
});

test('locales table can be displayed', function () {
    $this->get(LocaleResource::getUrl())
        ->assertSuccessful();
});

test('form validation works', function () {
    $component = livewire(ListLocales::class);

    // Check if create action is visible
    $component->assertActionVisible('create');

    // Mount the create action
    $component->mountAction('create');

    // Test required fields
    $component->callMountedAction()
        ->assertHasActionErrors([
            'id' => 'required',
            'name' => 'required',
            'native_name' => 'required',
            'system_locale' => 'required',
            'datetime_format' => 'required',
            'date_format' => 'required',
            'time_format' => 'required',
        ]);

    // Test with valid data
    $validData = Locale::factory()->definition();
    $validData['system_locale'] = 'en_US.UTF-8';
    $component->mountAction('create')
        ->setActionData($validData)
        ->callMountedAction()
        ->assertHasNoActionErrors();
});

test('new locale can be created', function () {
    $data = Locale::factory()->definition();

    // Remove is_active and is_available_in_panel attributes, since they're not used when creating a locale
    unset($data['is_active']);
    unset($data['is_available_in_panel']);
    $data['system_locale'] = 'en_US.UTF-8';

    livewire(ListLocales::class)
        ->mountAction('create')
        ->setActionData($data)
        ->callMountedAction()
        ->assertHasNoActionErrors();

    $locale = Locale::where('id', $data['id'])->first();
    expect($locale)->toBeObject();

    foreach ($data as $key => $val) {
        expect($locale->$key)->toEqual($val, "Failed asserting that attribute $key value ".$locale->$key.' is equal to '.$val);
    }
});

test('existing locale can be updated', function () {
    $locale = Locale::factory()->create();

    $new_data = Arr::except(Locale::factory()->definition(), ['id', 'is_active', 'is_available_in_panel']);
    $new_data['system_locale'] = 'en_US.UTF-8';

    livewire(ListLocales::class)
        ->callTableAction('edit', $locale, $new_data)
        ->assertHasNoTableActionErrors();

    $locale->refresh();

    foreach ($new_data as $key => $val) {
        expect($locale->$key)->toEqual($val);
    }
});

test('locale can be deleted', function () {
    $locale = Locale::factory()->create();

    livewire(ListLocales::class)
        ->callTableAction('delete', $locale)
        ->assertHasNoTableActionErrors();

    expect(Locale::find($locale->id))->toBeNull();
});
