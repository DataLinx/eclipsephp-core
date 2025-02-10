<?php

namespace Eclipse\Core\Database\Seeders;

use Eclipse\Core\Models\Locale;
use Illuminate\Database\Seeder;

class LocaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert English locale
        Locale::create([
            'id' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'system_locale' => 'en_GB.UTF8',
            'is_available_in_panel' => true,
            'datetime_format' => 'd/m/Y, H:i',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
        ]);

        // Insert Slovenian locale
        Locale::create([
            'id' => 'sl',
            'name' => 'Slovenian',
            'native_name' => 'Slovenščina',
            'system_locale' => 'sl_SI.UTF8',
            'is_available_in_panel' => true,
            'datetime_format' => 'd.m.Y, H:i',
            'date_format' => 'd.m.Y',
            'time_format' => 'H:i',
        ]);

        // Insert Bosnian locale
        Locale::create([
            'id' => 'bs',
            'name' => 'Bosnian',
            'native_name' => 'Bosanski',
            'system_locale' => 'bs_BA.UTF8',
            'datetime_format' => 'd.m.Y, H:i',
            'date_format' => 'd.m.Y',
            'time_format' => 'H:i',
        ]);

        // Insert Croatian locale
        Locale::create([
            'id' => 'hr',
            'name' => 'Croatian',
            'native_name' => 'Hrvatski',
            'system_locale' => 'hr_HR.UTF8',
            'is_available_in_panel' => true,
            'datetime_format' => 'd.m.Y, H:i',
            'date_format' => 'd.m.Y',
            'time_format' => 'H:i',
        ]);

        // Insert Serbian locale
        Locale::create([
            'id' => 'sr',
            'name' => 'Serbian',
            'native_name' => 'Srpski',
            'system_locale' => 'sr_RS.utf8@latin',
            'is_available_in_panel' => true,
            'datetime_format' => 'd.m.Y, H:i',
            'date_format' => 'd.m.Y',
            'time_format' => 'H:i',
        ]);

        // Insert German locale
        Locale::create([
            'id' => 'de',
            'name' => 'German',
            'native_name' => 'Deutsch',
            'system_locale' => 'de_DE.UTF8',
            'datetime_format' => 'd.m.Y, H:i',
            'date_format' => 'd.m.Y',
            'time_format' => 'H:i',
        ]);

        // Insert Italian locale
        Locale::create([
            'id' => 'it',
            'name' => 'Italian',
            'native_name' => 'Italiano',
            'system_locale' => 'it_IT.UTF8',
            'datetime_format' => 'd/m/Y, H:i',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
        ]);
    }
}
