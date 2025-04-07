<?php

namespace Eclipse\Core;

use Eclipse\Core\Console\Commands\ClearCommand;
use Eclipse\Core\Console\Commands\DeployCommand;
use Eclipse\Core\Console\Commands\PostComposerUpdate;
use Eclipse\Core\Models\User;
use Eclipse\Core\Providers\AdminPanelProvider;
use Eclipse\Core\Providers\HorizonServiceProvider;
use Eclipse\Core\Providers\TelescopeServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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
                'horizon',
                'permission',
                'telescope',
            ])
            ->discoversMigrations()
            ->runsMigrations()
            ->hasTranslations();
    }

    public function register(): self
    {
        parent::register();

        require_once __DIR__.'/Helpers/helpers.php';

        Event::listen(Login::class, function ($event) {
            if ($event->user instanceof User) {
                $event->user->updateLoginTracking();
            }
        });

        $this->app->register(AdminPanelProvider::class);

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->register(HorizonServiceProvider::class);

        return $this;
    }

    public function boot(): void
    {
        parent::boot();

        // Enable Model strictness when not in production
        Model::shouldBeStrict(! app()->isProduction());

        // Do not allow destructive DB commands in production
        DB::prohibitDestructiveCommands(app()->isProduction());
    }
}
