<?php

namespace Eclipse\Core\Database\Seeders;

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

        // Sites
        $this->call(SiteSeeder::class);

        // Users
        $this->call(UserSeeder::class);
    }
}
