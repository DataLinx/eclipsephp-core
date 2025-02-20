<?php

namespace Eclipse\Core;

use Eclipse\Core\Console\Commands\ClearCommand;
use Eclipse\Core\Console\Commands\DeployCommand;
use Eclipse\Core\Console\Commands\PostComposerInstall;
use Eclipse\Core\Console\Commands\PostComposerUpdate;
use Eclipse\Core\Models\User;
use Eclipse\Core\Policies\UserPolicy;
use Eclipse\Core\Providers\AdminPanelProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
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

        if ($this->isPanelRequest()) {
            $this->app->register(AdminPanelProvider::class);
        }

        return $this;
    }

    public function boot()
    {
        parent::boot();

        // Manually register user policy, since it can't be auto-discovered in the current setup
        Gate::policy(User::class, UserPolicy::class);

        return $this;
    }

    protected function isPanelRequest(): bool
    {
        if (Str::startsWith(request()->path(), 'admin')) {
            return true;
        }

        // If the request Referer header contains the admin path, return true
        if (Str::contains(request()->header('referer'), 'admin')) {
            return true;
        }

        // If running tests, always return true to make the panel available
        if ($this->app->runningInConsole() && config('app.env') === 'testing') {
            return true;
        }

        return false;
    }
}
