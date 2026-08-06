<?php

namespace Eclipse\Core\Database\Seeders;

use Illuminate\Database\Seeder;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LocaleSeeder::class);

        // Sites
        $this->call(SiteSeeder::class);

        // Users
        $this->call(UserSeeder::class);
    }
}
