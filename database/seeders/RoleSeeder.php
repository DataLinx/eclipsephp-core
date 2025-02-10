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
        Role::factory()
            ->count(5)
            ->create();
    }
}
