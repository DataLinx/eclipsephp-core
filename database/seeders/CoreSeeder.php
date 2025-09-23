<?php

namespace Eclipse\Core\Database\Seeders;

use Eclipse\Core\Models\Site;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        // Seed locales
        $this->call(LocaleSeeder::class);

        // Seed sites
        $this->call(SiteSeeder::class);

        // Set permissions team ID
        setPermissionsTeamId(Site::first()->id);

        // Seed roles and permissions with Filament Shield plugin
        Artisan::call('shield:generate', [
            '--all' => null,
            '--panel' => 'admin',
            '--option' => 'permissions',
        ]);

        // Seed additional roles
        $this->call(RoleSeeder::class);

        // Seed users
        $this->call(UserSeeder::class);
    }
}
