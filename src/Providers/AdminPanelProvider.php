<?php

namespace Eclipse\Core\Providers;

use Astrotomic\Translatable\Translatable;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Middleware\SyncShieldTenant;
use CactusGalaxy\FilamentAstrotomic\FilamentAstrotomicTranslatablePlugin;
use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Eclipse\Core\Filament\Pages\EditProfile;
use Eclipse\Core\Http\Middleware\SetupPanel;
use Eclipse\Core\Models\Locale;
use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Eclipse\Core\Models\User\Permission;
use Eclipse\Core\Models\User\Role;
use Eclipse\Core\Policies\User\RolePolicy;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Enums\Platform;
use Filament\Support\Facades\FilamentView;
use Filament\Tables;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $package_src = __DIR__.'/../../src/';

        return $panel
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
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverResources(in: $package_src.'Filament/Resources', for: 'Eclipse\\Core\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverPages(in: $package_src.'Filament/Pages', for: 'Eclipse\\Core\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->globalSearchKeyBindings(['ctrl+k', 'command+k'])
            ->globalSearchFieldSuffix(fn (): ?string => match (Platform::detect()) {
                Platform::Windows, Platform::Linux => 'CTRL+K',
                Platform::Mac => '⌘K',
                default => null,
            })
            ->maxContentWidth(MaxWidth::Full)
            ->simplePageMaxContentWidth(MaxWidth::Medium)
            ->tenant(Site::class, slugAttribute: 'domain')
            ->tenantDomain('{tenant:domain}')
            ->tenantMiddleware([
                SyncShieldTenant::class,
            ], isPersistent: true)
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
                SetupPanel::class,
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
                FilamentAstrotomicTranslatablePlugin::make(),
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
            ]);
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
        // For unit tests...
        if (app()->runningUnitTests()) {
            // Set the correct user model in auth config
            Config::set('auth.providers.users.model', User::class);
        }

        // Set up Spatie Laravel permissions
        app(\Spatie\Permission\PermissionRegistrar::class)
            ->setPermissionClass(Permission::class)
            ->setRoleClass(Role::class);

        // Prohibit Filament Shield's destructive commands in production
        FilamentShield::prohibitDestructiveCommands($this->app->isProduction());

        // Load customized translations for Filament Shield
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang/vendor/filament-shield', 'filament-shield');

        // Configure search for translatable model attributes
        Tables\Columns\TextColumn::configureUsing(function (Tables\Columns\TextColumn $column): void {
            if (Str::match('@^translations?\.(\w+)$@', $column->getName())) {
                $column
                    ->searchable(query: function (Builder $query, string $search) use ($column): Builder {
                        $columnName = Str::after($column->getName(), '.');
                        if ($query->hasNamedScope('whereTranslationLike')) {
                            /* @var Translatable $query */
                            return $query->whereTranslationLike($columnName, "%{$search}%");
                        }

                        return $query->where($columnName, 'like', "%{$search}%");
                    });
            }
        });

        // Configure language switcher
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $availableLocales = Locale::getAvailableLocales();

            $switch
                ->locales($availableLocales->pluck('id')->toArray())
                ->labels($availableLocales->pluck('native_name', 'id')->toArray());
        });

        // Set tenancy to off for all resources by default
        Resource::scopeToTenant(false);

        // Register policies for classes that can't be guessed automatically
        Gate::policy(Role::class, RolePolicy::class);
    }
}
