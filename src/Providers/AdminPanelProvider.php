<?php

namespace Eclipse\Core\Providers;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Middleware\SyncShieldTenant;
use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Eclipse\Common\Providers\GlobalSearchProvider;
use Eclipse\Core\Filament\Pages\EditProfile;
use Eclipse\Core\Filament\Pages\Tools\HealthCheckResults;
use Eclipse\Core\Models\Locale;
use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Eclipse\Core\Services\Registry;
use Eclipse\World\EclipseWorld;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Livewire\Notifications;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Platform;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Support\Facades\FilamentView;
use Filament\Widgets;
use Hasnayeen\Themes\Http\Middleware\SetTheme;
use Hasnayeen\Themes\ThemesPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $package_src = __DIR__.'/../../src/';

        // Get locales if the table exists, otherwise fallback to the default locale (when database is not set up yet)
        if (Schema::hasTable('locales')) {
            $localeIds = Locale::getAvailableLocales()->pluck('id')->toArray();
        } else {
            $localeIds = [config('app.locale', 'en')];
        }

        $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->profile(EditProfile::class)
            ->colors([
                'primary' => Color::Cyan,
                'gray' => Color::Slate,
            ])
            ->topNavigation()
            ->brandName(fn () => Registry::getSite()->name)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverResources(in: $package_src.'Filament/Resources', for: 'Eclipse\\Core\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverPages(in: $package_src.'Filament/Pages', for: 'Eclipse\\Core\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverClusters(in: $package_src.'Filament/Clusters', for: 'Eclipse\\Core\\Filament\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->globalSearch(GlobalSearchProvider::class)
            ->globalSearchKeyBindings(['ctrl+k', 'command+k'])
            ->globalSearchFieldSuffix(fn (): ?string => match (Platform::detect()) {
                Platform::Windows, Platform::Linux => 'CTRL+K',
                Platform::Mac => '⌘K',
                default => null,
            })
            ->tenant(Site::class, slugAttribute: 'domain')
            ->tenantDomain('{tenant:domain}')
            ->tenantMiddleware([
                SyncShieldTenant::class,
                SetTheme::class,
            ], isPersistent: true)
            ->tenantMenu(config('eclipse.multi_site', false))
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                EnvironmentIndicatorPlugin::make(),
                FilamentDeveloperLoginsPlugin::make()
                    ->enabled(app()->isLocal())
                    ->modelClass(User::class)
                    ->users(config('eclipse.developer_logins') ?: []),
                EclipseWorld::make(),
                SpatieLaravelTranslatablePlugin::make()
                    ->defaultLocales($localeIds),
                ThemesPlugin::make(),
                FilamentSpatieLaravelHealthPlugin::make()
                    ->usingPage(HealthCheckResults::class)
                    ->authorize(fn (): bool => auth()->user()->hasRole('super_admin')),
            ])
            ->navigationGroups([
                NavigationGroup::make('Users'),
                NavigationGroup::make('Configuration'),
                NavigationGroup::make('Tools'),
            ])
            ->navigationItems([
                NavigationItem::make('Telescope')
                    ->url('/telescope', shouldOpenInNewTab: true)
                    ->icon('heroicon-s-arrow-top-right-on-square')
                    ->group('Tools')
                    ->sort(1000)
                    ->hidden(fn (): bool => ! config('telescope.enabled', false)),
                NavigationItem::make('Horizon')
                    ->url('/horizon', shouldOpenInNewTab: true)
                    ->icon('heroicon-s-arrow-top-right-on-square')
                    ->group('Tools')
                    ->sort(2000)
                    // Always visible for local env, otherwise the viewHorizon permission is required
                    ->visible(fn (User $user): bool => app()->isLocal() || $user->can('viewHorizon')),
                NavigationItem::make('Log viewer')
                    ->url('/'.config('log-viewer.route_path', 'log-viewer'), shouldOpenInNewTab: true)
                    ->icon('heroicon-s-arrow-top-right-on-square')
                    ->group('Tools')
                    ->sort(3000)
                    ->hidden(fn (): bool => ! config('log-viewer.enabled', false) || ! auth()->user()->hasRole('super_admin')),
            ])
            ->databaseNotifications()
            ->unsavedChangesAlerts();

        // Add plugins from the plugin registry
        foreach (app(Registry::class)->getPlugins() as $plugin) {
            $panel->plugin($plugin);
        }

        if (config('eclipse.tools.phpmyadmin')) {
            $panel->navigationItems([
                NavigationItem::make('phpMyAdmin')
                    ->url(config('eclipse.tools.phpmyadmin'), shouldOpenInNewTab: true)
                    ->icon('heroicon-s-arrow-top-right-on-square')
                    ->group('Tools')
                    ->sort(900),
            ]);
        }

        // Configure notifications
        Notifications::alignment(Alignment::Center);
        Notifications::verticalAlignment(VerticalAlignment::End);

        return $panel;
    }

    public function register(): void
    {
        parent::register();

        FilamentView::registerRenderHook('panels::body.end', fn (): string => Blade::render("@vite('resources/js/app.js')"));
    }

    /**
     * Bootstrap any admin-specific services.
     */
    public function boot(): void
    {
        // Prohibit Filament Shield's destructive commands in production
        FilamentShield::prohibitDestructiveCommands($this->app->isProduction());

        // Load customized translations for Filament Shield
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang/vendor/filament-shield', 'filament-shield');
    }
}
