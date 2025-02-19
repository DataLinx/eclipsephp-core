<?php

namespace Eclipse\Core\Providers;

use Astrotomic\Translatable\Translatable;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use CactusGalaxy\FilamentAstrotomic\FilamentAstrotomicTranslatablePlugin;
use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Eclipse\Core\Filament\Pages\EditProfile;
use Eclipse\Core\Models\Locale;
use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Eclipse\Core\Models\User\Permission;
use Eclipse\Core\Models\User\Role;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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
use Illuminate\Support\Str;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;
use Spatie\Permission\PermissionServiceProvider;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
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
            ->discoverResources(in: base_path('vendor/eclipsephp/core/src/Filament/Resources'), for: 'Eclipse\\Core\\Filament\\Resources')
//            ->discoverResources(in: base_path('vendor/eclipsephp/crm/src/Filament/Resources'), for: 'Eclipse\\CRM\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverPages(in: base_path('vendor/eclipsephp/core/src/Filament/Pages'), for: 'Eclipse\\Core\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->globalSearchKeyBindings(['ctrl+k', 'command+k'])->globalSearchFieldSuffix(fn (): ?string => match (Platform::detect()) {
                Platform::Windows, Platform::Linux => 'CTRL+K',
                Platform::Mac => '⌘K',
                default => null,
            })
            ->maxContentWidth(MaxWidth::Full)
            ->simplePageMaxContentWidth(MaxWidth::Medium)
            ->tenant(Site::class, slugAttribute: 'domain')
            ->tenantDomain('{tenant:domain}')
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
                EnvironmentIndicatorPlugin::make(),
                FilamentDeveloperLoginsPlugin::make()
                    ->enabled(app()->environment('local'))
                    ->modelClass(User::class)
                    ->users([
                        'Super admin' => 'test@datalinx.si',
                    ]),
                FilamentAstrotomicTranslatablePlugin::make(),
            ]);
    }

    public function register(): void
    {
        parent::register();

        $this->app->register(PermissionServiceProvider::class);

        FilamentView::registerRenderHook('panels::body.end', fn (): string => Blade::render("@vite('resources/js/app.js')"));
    }

    /**
     * Bootstrap any admin-specific services.
     */
    public function boot(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)
            ->setPermissionClass(Permission::class)
            ->setRoleClass(Role::class);

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

        setPermissionsTeamId(1);

        // Set available languages for the Translatable package
        Config::set('translatable.locales', Locale::getAvailableLocales()->pluck('id')->toArray());
    }
}
