<?php

namespace Workbench\App\Providers;

use Eclipse\Common\CommonServiceProvider;
use Eclipse\Core\Providers\AdminPanelProvider;
use Eclipse\Frontend\Providers\FrontendPanelProvider;
use Illuminate\Support\ServiceProvider;
use Nben\FilamentRecordNav\FilamentRecordNavServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        if (class_exists(CommonServiceProvider::class)) {
            $this->app->register(CommonServiceProvider::class);
        }

        if (class_exists(FilamentRecordNavServiceProvider::class)) {
            $this->app->register(FilamentRecordNavServiceProvider::class);
        }

        $this->app->register(AdminPanelProvider::class);

        if (class_exists(FrontendPanelProvider::class)) {
            $this->app->register(FrontendPanelProvider::class);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
