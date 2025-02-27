<?php

namespace Eclipse\Core\Database\Seeders;

use Eclipse\Core\Models\Site;
use Illuminate\Database\Seeder;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LocaleSeeder::class);

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
