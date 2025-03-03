<?php

namespace Eclipse\Core\Database\Seeders;

use Eclipse\Core\Models\Site;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LocaleSeeder::class);

        // Seed roles and permissions with Filament Shield plugin
        Artisan::call('shield:generate', [
            '--all' => null,
            '--panel' => 'admin',
            '--option' => 'permissions',
            '--minimal' => null,
        ]);

        // Seed additional roles
        $this->call(RoleSeeder::class);

        // Create main site
        $site = Site::create([
            'domain' => basename(config('app.url')),
            'name' => config('app.name'),
        ]);

        setPermissionsTeamId($site->id);

        // Users
        $this->call(UserSeeder::class);
    }
}
