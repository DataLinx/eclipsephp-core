<?php

namespace Eclipse\Core;

use Eclipse\Core\Console\Commands\ClearCommand;
use Eclipse\Core\Console\Commands\DeployCommand;
use Eclipse\Core\Console\Commands\PostComposerInstall;
use Eclipse\Core\Console\Commands\PostComposerUpdate;
use Eclipse\Core\Providers\AdminPanelProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class EclipseServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('core')
            ->hasCommands([
                ClearCommand::class,
                DeployCommand::class,
                PostComposerInstall::class,
                PostComposerUpdate::class,
            ])
            ->hasConfigFile([
                'eclipse',
                'filament-shield',
                'permission',
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

        return $this;
    }
}
