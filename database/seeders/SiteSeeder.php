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
        if (config('eclipse.multi_site')) {
            // Create sites from seeder config
            $config = config('eclipse.seed.sites');

            // Create preset sites
            if (! empty($config['presets'])) {
                foreach ($config['presets'] as $preset) {
                    Site::create($preset['data']);
                }
            }

            // Create random sites
            if (! empty($config['count']) && $config['count'] > 0) {
                Site::factory()->count($config['count'])->create();
            }
        } else {
            // Create default site from config data
            Site::create([
                'domain' => basename(config('app.url')),
                'name' => config('app.name'),
            ]);
        }
    }
}
