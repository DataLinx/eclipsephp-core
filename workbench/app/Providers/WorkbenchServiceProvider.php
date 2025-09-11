<?php

namespace Workbench\App\Providers;

use BezhanSalleh\PanelSwitch\PanelSwitchServiceProvider;
use Eclipse\Common\CommonServiceProvider;
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

        if (class_exists(PanelSwitchServiceProvider::class)) {
            $this->app->register(PanelSwitchServiceProvider::class);
        }

        if (class_exists(FilamentRecordNavServiceProvider::class)) {
            $this->app->register(FilamentRecordNavServiceProvider::class);
        }

        $this->app->register(\Eclipse\Core\Providers\AdminPanelProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
