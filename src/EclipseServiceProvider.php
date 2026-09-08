<?php

namespace Eclipse\Core;

use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Eclipse\Common\Foundation\Providers\PackageServiceProvider;
use Eclipse\Common\Helpers\L10nHelper;
use Eclipse\Common\Package;
use Eclipse\Core\Console\Commands\ClearCommand;
use Eclipse\Core\Console\Commands\DeployCommand;
use Eclipse\Core\Console\Commands\PostComposerUpdate;
use Eclipse\Core\Console\Commands\SetupReverb;
use Eclipse\Core\Health\Checks\ReverbCheck;
use Eclipse\Core\Listeners\LogEmailToDatabase;
use Eclipse\Core\Listeners\SendEmailSuccessNotification;
use Eclipse\Core\Models\User;
use Eclipse\Core\Notifications\Channels\SiteDatabaseChannel;
use Eclipse\Core\Providers\AdminPanelProvider;
use Eclipse\Core\Providers\HorizonServiceProvider;
use Eclipse\Core\Providers\TelescopeServiceProvider;
use Eclipse\Core\Services\Registry;
use Eclipse\Frontend\Providers\FrontendPanelProvider;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Columns\Column;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\HorizonCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\RedisCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;
use Spatie\LaravelPackageTools\Package as SpatiePackage;
use Spatie\SecurityAdvisoriesHealthCheck\SecurityAdvisoriesCheck;

class EclipseServiceProvider extends PackageServiceProvider
{
    public function configurePackage(SpatiePackage|Package $package): void
    {
        $package->name('eclipse')
            ->hasCommands([
                ClearCommand::class,
                DeployCommand::class,
                SetupReverb::class,
                PostComposerUpdate::class,
            ])
            ->hasConfigFile([
                'blade-heroicons',
                'eclipse',
                'horizon',
                'log-viewer',
                'settings',
                'telescope',
                'health',
            ])
            ->hasViews()
            ->hasSettings()
            ->discoversMigrations()
            ->runsMigrations()
            ->hasTranslations()
            ->hasRoute('console');
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

        Event::listen(MessageSent::class, SendEmailSuccessNotification::class);
        Event::listen(MessageSent::class, LogEmailToDatabase::class);

        if ($this->app->runningInConsole() || $this->isAdminRequest()) {
            $this->app->register(AdminPanelProvider::class);
        }

        if (class_exists(FrontendPanelProvider::class)) {
            $this->app->register(FrontendPanelProvider::class);
        }

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->register(HorizonServiceProvider::class);

        $this->app->singleton(Registry::class, function () {
            return new Registry;
        });

        $this->app->bind(DatabaseChannel::class, SiteDatabaseChannel::class);

        return $this;
    }

    public function boot(): void
    {
        parent::boot();

        // For unit tests...
        if (app()->runningUnitTests()) {
            // Set the correct user model in auth config
            Config::set('auth.providers.users.model', User::class);
        }

        // Enable Model strictness when not in production
        Model::shouldBeStrict(! app()->isProduction());

        // Do not allow destructive DB commands in production
        DB::prohibitDestructiveCommands(app()->isProduction());

        // Set tenancy to off for all resources by default
        Resource::scopeToTenant(false);

        // Set common settings for Filament table columns
        Column::configureUsing(function (Column $column) {
            $column
                ->toggleable()
                ->sortable();
        });

        // Configure language switcher
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->locales(array_keys(L10nHelper::getLocaleOptions()));
        });

        // Register tenant and user IDs in Filament script data
        FilamentAsset::registerScriptData([
            'user' => ['id' => auth()->id()],
            'tenant' => ['id' => Filament::getTenant()?->getKey()],
        ]);

        // Register health checks
        Health::checks([
            OptimizedAppCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
            UsedDiskSpaceCheck::new()
                ->warnWhenUsedSpaceIsAbovePercentage(70)
                ->failWhenUsedSpaceIsAbovePercentage(90),
            CacheCheck::new(),
            HorizonCheck::new(),
            ReverbCheck::new(),
            RedisCheck::new(),
            ScheduleCheck::new(),
            SecurityAdvisoriesCheck::new(),
        ]);

        // Set Livewire's update route with admin path
        Livewire::setUpdateRoute(function ($handle, $path) {
            return Route::post("/admin$path", $handle)
                ->middleware(['web']);
        });
    }

    private function isAdminRequest(): bool
    {
        $uri = explode('/', trim(request()->getRequestUri(), '/'));

        return $uri[0] === 'admin' || $uri[0] === 'filament-developer-logins';
    }
}
