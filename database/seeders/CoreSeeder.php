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
        ]);

        // Seed additional roles
        $this->call(RoleSeeder::class);

        // Sites
        $this->call(SiteSeeder::class);

        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        // Users
        $this->call(UserSeeder::class);
    }

    private function assignPermissionsToRoles(): void
    {
        $allPermissions = \Eclipse\Core\Models\User\Permission::all();
        $primarySite = Site::first();

        $superAdminRoles = \Eclipse\Core\Models\User\Role::where('name', 'super_admin')->get();
        $adminRoles = \Eclipse\Core\Models\User\Role::where('name', 'admin')->get();

        foreach ($superAdminRoles as $role) {
            if (! $role->site_id) {
                $role->site_id = $primarySite->id;
                $role->save();
            }
            $role->syncPermissions($allPermissions);
        }

        foreach ($adminRoles as $role) {
            if (! $role->site_id) {
                $role->site_id = $primarySite->id;
                $role->save();
            }
            $role->syncPermissions($allPermissions);
        }
    }
}
