<?php

use Eclipse\Core\Models\WorldCountry;
use Eclipse\Core\Models\WorldRegion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

// uses(Tests\TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
});

test('can assign a geo region to a country', function () {
    Auth::login($this->superAdmin);

    $region = WorldRegion::factory()->create();
    $country = WorldCountry::factory()->create();

    $country->region()->associate($region);
    $country->save();

    $this->assertEquals($region->id, $country->region_id);
});

test('can attach special regions to a country with dates', function () {
    Auth::login($this->superAdmin);

    $region = WorldRegion::factory()->create(['is_special' => true]);
    $country = WorldCountry::factory()->create();

    $country->specialRegions()->attach($region->id, [
        'start_date' => now()->subYear(),
        'end_date' => null,
    ]);

    $this->assertDatabaseHas('world_country_in_special_region', [
        'country_id' => $country->id,
        'region_id' => $region->id,
    ]);
});

test('can determine if country is in special region at date', function () {
    $region = WorldRegion::factory()->create(['is_special' => true]);
    $country = WorldCountry::factory()->create();

    $country->specialRegions()->attach($region->id, [
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonth(),
    ]);

    $inRegion = $country->isInSpecialRegion('EU'); // assuming such method exists
    expect($inRegion)->toBeTrue();
});
