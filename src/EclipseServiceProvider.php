<?php

namespace Eclipse\Core;

use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Eclipse\Common\Foundation\Providers\PackageServiceProvider;
use Eclipse\Common\Package;
use Eclipse\Core\Console\Commands\ClearCommand;
use Eclipse\Core\Console\Commands\DeployCommand;
use Eclipse\Core\Console\Commands\PostComposerUpdate;
use Eclipse\Core\Console\Commands\SetupReverb;
use Eclipse\Core\Filament\Resources\LocaleResource;
use Eclipse\Core\Filament\Resources\MailLogResource;
use Eclipse\Core\Filament\Resources\SiteResource;
use Eclipse\Core\Filament\Resources\UserResource;
use Eclipse\Core\Health\Checks\ReverbCheck;
use Eclipse\Core\Listeners\LogEmailToDatabase;
use Eclipse\Core\Listeners\SendEmailSuccessNotification;
use Eclipse\Core\Models\Locale;
use Eclipse\Core\Models\User;
use Eclipse\Core\Models\User\Permission;
use Eclipse\Core\Models\User\Role;
use Eclipse\Core\Notifications\Channels\SiteDatabaseChannel;
use Eclipse\Core\Policies\User\RolePolicy;
use Eclipse\Core\Providers\AdminPanelProvider;
use Eclipse\Core\Providers\HorizonServiceProvider;
use Eclipse\Core\Providers\TelescopeServiceProvider;
use Eclipse\Core\Services\Registry;
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
use Illuminate\Support\Facades\Gate;
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
use Spatie\Permission\PermissionRegistrar;
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
                'filament-shield',
                'horizon',
                'log-viewer',
                'permission',
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

        // Merge per-resource abilities into the effective config
        $this->app->booted(function () {
            $manage = config('filament-shield.resources.manage', []);

            $pluginManage = [

                RoleResource::class => [
                    'viewAny',
                    'view',
                    'create',
                    'update',
                    'delete',
                ],
                MailLogResource::class => [
                    'viewAny',
                    'view',
                ],
                LocaleResource::class => [
                    'viewAny',
                    'create',
                    'update',
                    'delete',
                    'deleteAny',
                ],
                SiteResource::class => [
                    'viewAny',
                    'create',
                    'update',
                    'delete',
                    'deleteAny',
                ],
                UserResource::class => [
                    'viewAny',
                    'view',
                    'create',
                    'update',
                    'delete',
                    'deleteAny',
                    'restore',
                    'restoreAny',
                    'forceDelete',
                    'forceDeleteAny',
                    'impersonate',
                    'sendEmail',
                ],
            ];

            config()->set('filament-shield.resources.manage', array_replace_recursive(
                $manage,
                $pluginManage,
            ));
        });

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

        // Set up Spatie Laravel permissions
        app(PermissionRegistrar::class)
            ->setPermissionClass(Permission::class)
            ->setRoleClass(Role::class);

        // Register policies for classes that can't be guessed automatically
        Gate::policy(Role::class, RolePolicy::class);

        // Set common settings for Filament table columns
        Column::configureUsing(function (Column $column) {
            $column
                ->toggleable()
                ->sortable();
        });

        // Configure language switcher
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $availableLocales = Locale::getAvailableLocales();

            $switch
                ->locales($availableLocales->pluck('id')->toArray())
                ->labels($availableLocales->pluck('native_name', 'id')->toArray());
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
        if ($this->isAdminRequest()) {
            Livewire::setUpdateRoute(function ($handle) {
                return Route::post('/admin/livewire/update', $handle)
                    ->middleware(['web']);
            });
        }
    }

    private function isAdminRequest(): bool
    {
        $uri = explode('/', trim(request()->getRequestUri(), '/'));

        return $uri[0] === 'admin' || $uri[0] === 'filament-developer-logins';
    }
}
