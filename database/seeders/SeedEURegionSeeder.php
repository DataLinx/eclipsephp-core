<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Eclipse\Core\Models\WorldRegion;
use Eclipse\Core\Models\WorldCountry;

class SeedEURegionSeeder extends Seeder
{
    public function run(): void
    {
        $eu = WorldRegion::firstOrCreate(
            ['code' => 'EU'],
            ['name' => 'European Union', 'is_special' => true]
        );

        $euCountryCodes = [
            'FR', 'DE', 'IT', 'ES', 'NL', 'BE', 'PL', 'SE', 'AT', 'FI', 'DK',
            'IE', 'PT', 'CZ', 'SK', 'HU', 'RO', 'BG', 'HR', 'GR', 'SI',
            'LT', 'LV', 'EE', 'CY', 'LU', 'MT'
        ];

        $countries = WorldCountry::whereIn('code', $euCountryCodes)->get();

        foreach ($countries as $country) {
            $country->specialRegions()->syncWithoutDetaching([
                $eu->id => ['start_date' => now()]
            ]);
        }
    }
}
