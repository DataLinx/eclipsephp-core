<?php

use Eclipse\Core\Filament\Resources\LocaleResource;
use Eclipse\Core\Filament\Resources\LocaleResource\Pages\ListLocales;
use Eclipse\Core\Models\Locale;
use Illuminate\Support\Arr;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->setUpUserAndTenant();
    LocaleResource::canViewAny();
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
    $validData['system_locale'] = array_rand(LocaleResource::getSystemLocales());
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
    $data['system_locale'] = array_rand(LocaleResource::getSystemLocales());

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
    $new_data['system_locale'] = array_rand(LocaleResource::getSystemLocales());

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
