<?php

namespace Eclipse\Core;

use Eclipse\Core\Console\Commands\ClearCommand;
use Eclipse\Core\Console\Commands\DeployCommand;
use Eclipse\Core\Console\Commands\PostComposerUpdate;
use Eclipse\Core\Providers\AdminPanelProvider;
use Eclipse\Core\Providers\TelescopeServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class EclipseServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('eclipse')
            ->hasCommands([
                ClearCommand::class,
                DeployCommand::class,
                PostComposerUpdate::class,
            ])
            ->hasConfigFile([
                'blade-heroicons',
                'eclipse',
                'filament-shield',
                'permission',
                'telescope',
            ])
            ->hasMigrations([
                'add_login_tracking_to_users',
            ])
            ->discoversMigrations()
            ->runsMigrations()
            ->hasTranslations();
    }

    public function register()
    {
        parent::register();

        require_once __DIR__.'/Helpers/helpers.php';

        $this->app->register(AdminPanelProvider::class);

        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        return $this;
    }
}
