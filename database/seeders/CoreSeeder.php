<?php

namespace Eclipse\Core\Database\Seeders;

use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User\Permission;
use Eclipse\Core\Models\User\Role;
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
        $allPermissions = Permission::all();

        foreach (Site::all() as $site) {
            foreach (['super_admin', 'admin'] as $roleName) {
                $role = Role::firstOrCreate([
                    'name' => $roleName,
                    'guard_name' => 'web',
                    'site_id' => $site->id,
                ]);

                $role->syncPermissions($allPermissions);
            }
        }

        $this->assignCustomPermissionsToRoles();
    }

    private function assignCustomPermissionsToRoles(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $guard = config('auth.defaults.guard', 'web');
        Permission::findOrCreate('impersonate_user', $guard);
        Permission::findOrCreate('send_email_user', $guard);
    }
}
