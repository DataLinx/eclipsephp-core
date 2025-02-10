<?php

namespace Eclipse\Core\Database\Seeders;

use Eclipse\Core\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Site::factory()
            ->count(5)
            ->create();
    }
}
