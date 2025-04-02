<?php

namespace Tests;

use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Filament\Facades\Filament;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use WithWorkbench;

    protected ?User $superAdmin = null;

    protected ?User $user = null;

    protected function setUp(): void
    {
        // Always show errors when testing
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        parent::setUp();

        $this->withoutVite();

        require_once __DIR__.'/../src/Helpers/helpers.php';
    }

    public function ignorePackageDiscoveriesFrom(): array
    {
        return [
            // A list of packages that should not be auto-discovered when running tests
            'laravel/telescope',
        ];
    }

    /**
     * Run database migrations
     */
    protected function migrate(): self
    {
        $this->artisan('migrate');

        return $this;
    }

    /**
     * Set up default "super admin" user and tenant (site)
     */
    protected function set_up_super_admin_and_tenant(): self
    {
        $site = Site::first();

        $this->superAdmin = User::factory()->make();
        $this->superAdmin->assignRole('super_admin')->save();
        $this->superAdmin->sites()->attach($site);

        $this->actingAs($this->superAdmin);

        Filament::setTenant($site);

        return $this;
    }

    /**
     * Set up a common user with no roles or permissions
     */
    protected function set_up_common_user_and_tenant(): self
    {
        $site = Site::first();

        $this->user = User::factory()->create();
        $this->user->sites()->attach($site);

        $this->actingAs($this->user);

        Filament::setTenant($site);

        return $this;
    }
}
