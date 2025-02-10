<?php

namespace Eclipse\Core;

use App\Models\User;
use Eclipse\Core\Console\Commands\ClearCommand;
use Eclipse\Core\Console\Commands\DeployCommand;
use Eclipse\Core\Console\Commands\PostComposerInstall;
use Eclipse\Core\Console\Commands\PostComposerUpdate;
use Eclipse\Core\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
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
            ->hasConfigFile('eclipse')
            ->discoversMigrations()
            ->runsMigrations()
            ->hasTranslations();
    }

    public function register()
    {
        parent::register();

        require_once __DIR__ . '/Helpers/helpers.php';

        return $this;
    }

    public function boot()
    {
        parent::boot();

        // Manually register user policy, since it can't be auto-discovered in the current setup
        Gate::policy(User::class, UserPolicy::class);

        return $this;
    }
}
