<?php

namespace Tests;

use Eclipse\Common\Foundation\Testing\TestCase as BaseTestCase;
use Eclipse\Core\Http\Middleware\SetupSite;
use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Eclipse\Core\Services\Registry;
use Filament\Facades\Filament;
use Illuminate\Contracts\Http\Kernel;

abstract class TestCase extends BaseTestCase
{
    protected $enablesPackageDiscoveries = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure SetupSite middleware is applied during tests
        // This is done here since the "withMiddleware" method in workbench/bootstrap/app.php does not seem to work
        // $this->withMiddleware(SetupTenant::class) also does not work
        app(Kernel::class)->pushMiddleware(SetupSite::class);

        require_once __DIR__.'/../src/Helpers/helpers.php';
    }

    /**
     * Set up a common user and tenant (site)
     */
    protected function setUpUserAndTenant(): self
    {
        $site = Site::first() ?? Site::factory()->create();

        $this->user = User::factory()->create();
        $this->user->sites()->attach($site);

        $this->actingAs($this->user);

        Filament::setTenant($site);
        Registry::setSite($site);

        return $this;
    }

    protected function userModel(): string
    {
        return User::class;
    }
}
