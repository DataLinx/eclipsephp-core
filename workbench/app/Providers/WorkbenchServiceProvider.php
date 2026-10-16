<?php

namespace Workbench\App\Providers;

use Eclipse\Core\EclipseServiceProvider;
use Eclipse\Core\Providers\AdminPanelProvider;
use Workbench\Database\Seeders\DatabaseSeeder;

class WorkbenchServiceProvider extends \Eclipse\Common\Workbench\Providers\WorkbenchServiceProvider
{
    protected bool $shouldLoadMigrations = false;

    /**
     * Register services.
     */
    public function register(): void
    {
        parent::register();

        $this->app->register(AdminPanelProvider::class, true);
        $this->app->register(EclipseServiceProvider::class);

        // Set default seeder class
        $this->app->bind('DatabaseSeeder', DatabaseSeeder::class);
    }
}
