<?php

namespace Eclipse\Core\Database\Seeders;

use Eclipse\Core\Models\User\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (is_array(config('eclipse.seed.roles.presets'))) {
            foreach (config('eclipse.seed.roles.presets') as $preset) {
                Role::create($preset['data']);
            }
        }

        if (config('eclipse.seed.roles.count') > 0) {
            Role::factory()
                ->count(config('eclipse.seed.roles.count'))
                ->create();
        }
    }
}
