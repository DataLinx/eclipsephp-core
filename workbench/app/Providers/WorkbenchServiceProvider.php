<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Common package service provider
        if (class_exists(\Eclipse\Common\CommonServiceProvider::class)) {
            $this->app->register(\Eclipse\Common\CommonServiceProvider::class);
        }

        // Register PanelSwitch service provider
        if (class_exists(\BezhanSalleh\PanelSwitch\PanelSwitchServiceProvider::class)) {
            $this->app->register(\BezhanSalleh\PanelSwitch\PanelSwitchServiceProvider::class);
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
