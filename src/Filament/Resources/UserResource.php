<?php

namespace Eclipse\Core\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Eclipse\Core\Filament\Exports\TableExport;
use Eclipse\Core\Filament\Resources;
use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Eclipse\Core\Models\User\Role;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $tenantOwnershipRelationshipName = 'sites';

    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Users';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Personal Info.'))
                ->columns(2)
                ->schema([
                    Forms\Components\SpatieMediaLibraryFileUpload::make('avatar')
                        ->collection('avatars')
                        ->avatar()
                        ->imageEditor()
                        ->columnSpanFull()
                        ->maxSize(1024 * 2),
                    self::getFirstNameFormComponent(),
                    self::getLastNameFormComponent(),
                    self::getEmailFormComponent(),
                    Forms\Components\DateTimePicker::make('email_verified_at')
                        ->visible(config('eclipse.email_verification'))
                        ->disabled(),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context): bool => $context === 'create')
                        ->label(fn (string $context): string => $context === 'create' ? 'Password' : 'Set new password')
                        ->suffixAction(
                            Action::make('randomPassword')
                                ->icon('heroicon-s-arrow-path')
                                ->tooltip(__('Random password generator'))
                                ->color('gray')
                                ->action(
                                    fn (Set $set) => $set('password', Str::password(16))
                                )
                        ),
                ]),
            Forms\Components\Section::make(__('Global Roles'))
                ->schema([
                    Forms\Components\CheckboxList::make('global_roles')
                        ->hiddenLabel()
                        ->columnSpanFull()
                        ->columns([
                            'sm' => 2,
                            'md' => 3,
                            'lg' => 4,
                            'xl' => 5,
                        ])
                        ->options(fn () => Role::pluck('name', 'id')->mapWithKeys(
                            fn ($name, $key) => [$key => Str::headline($name)]
                        ))
                        ->afterStateHydrated(function ($component, $record) {
                            if ($record) {
                                $roles = DB::table('model_has_roles')
                                    ->where('model_id', $record->id)
                                    ->where('model_type', get_class($record))
                                    ->where('is_global', true)
                                    ->pluck('role_id')
                                    ->toArray();
                                $component->state($roles);
                            }
                        }),
                ]),

            Forms\Components\Tabs::make()
                ->columnSpanFull()
                ->tabs(function (): array {
                    $tabs = [];
                    foreach (Site::all() as $site) {
                        $tabs[] = Forms\Components\Tabs\Tab::make($site->name)
                            ->schema([
                                Forms\Components\CheckboxList::make("site_{$site->id}")
                                    ->label('Roles')
                                    ->columns(3)
                                    ->options(fn () => Role::pluck('name', 'id')->mapWithKeys(
                                        fn ($name, $key) => [$key => Str::headline($name)]
                                    ))
                                    ->afterStateHydrated(function ($component, $record) use ($site) {
                                        if ($record) {
                                            $roles = DB::table('model_has_roles')
                                                ->where('model_id', $record->id)
                                                ->where('model_type', get_class($record))
                                                ->where(function ($query) use ($site) {
                                                    $query->where('is_global', true)
                                                        ->orWhere(function ($q) use ($site) {
                                                            $q->where('is_global', false)
                                                                ->where(config('permission.column_names.team_foreign_key'), $site->id);
                                                        });
                                                })
                                                ->pluck('role_id')
                                                ->unique()
                                                ->toArray();
                                            $component->state($roles);
                                        }
                                    }),
                            ]);
                    }

                    return $tabs;
                }),
            Forms\Components\SpatieMediaLibraryFileUpload::make('avatar')
                ->collection('avatars')
                ->avatar()
                ->imageEditor()
                ->maxSize(1024 * 2),
            self::getFirstNameFormComponent(),
            self::getLastNameFormComponent(),
            self::getEmailFormComponent(),
            Forms\Components\TextInput::make('phone_number')
                ->label('Phone')
                ->tel(),
            Forms\Components\DateTimePicker::make('email_verified_at')
                ->visible(config('eclipse.email_verification'))
                ->disabled(),
            Forms\Components\TextInput::make('password')
                ->password()
                ->revealable()
                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $context): bool => $context === 'create')
                ->label(fn (string $context): string => $context === 'create' ? 'Password' : 'Set new password')
                ->suffixAction(
                    Action::make('randomPassword')
                        ->icon('heroicon-s-arrow-path')
                        ->tooltip(__('Random password generator'))
                        ->color('gray')
                        ->action(
                            fn (Set $set) => $set('password', Str::password(16))
                        )
                ),
            Forms\Components\Select::make('country_id')
                ->relationship('country', 'name')
                ->preload()
                ->optionsLimit(20)
                ->searchable(),
            Forms\Components\DatePicker::make('date_of_birth')
                ->native(false)
                ->minDate(now()->subYears(80))
                ->maxDate(now()),
            Forms\Components\Select::make('roles')
                ->relationship('roles', 'name')
                ->saveRelationshipsUsing(function (User $record, $state) {
                    $record->roles()->syncWithPivotValues($state, [config('permission.column_names.team_foreign_key') => getPermissionsTeamId()]);
                })
                ->multiple()
                ->preload()
                ->searchable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        $columns = [
            SpatieMediaLibraryImageColumn::make('avatar')
                ->collection('avatars')
                ->toggleable()
                ->size(50)
                ->circular()
                ->defaultImageUrl(fn (User $user) => 'https://ui-avatars.com/api/?name='.urlencode($user->name)),
            Tables\Columns\TextColumn::make('first_name')
                ->searchable()
                ->sortable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('last_name')
                ->searchable()
                ->sortable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('name')
                ->label('Full name')
                ->searchable()
                ->sortable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('last_login_at')
                ->label('Last login')
                ->dateTime()
                ->sortable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('login_count')
                ->label('Total Logins')
                ->sortable()
                ->numeric()
                ->formatStateUsing(fn (?int $state) => $state ?? 0),
        ];

        if (config('eclipse.email_verification')) {
            $columns[] = Tables\Columns\TextColumn::make('email')
                ->searchable()
                ->sortable()
                ->width(150)
                ->icon(fn (User $user) => $user->email_verified_at ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle')
                ->iconColor(fn (User $user) => $user->email_verified_at ? Color::Green : Color::Red)
                ->tooltip(fn (User $user) => $user->email_verified_at ? 'Verified' : 'Not verified');
        } else {
            $columns[] = Tables\Columns\TextColumn::make('email')
                ->searchable()
                ->sortable()
                ->width(150);
        }

        $columns[] = Tables\Columns\TextColumn::make('phone_number')
            ->label('Phone');

        $columns[] = Tables\Columns\TextColumn::make('email_verified_at')
            ->label('Verified email')
            ->placeholder('Not verified')
            ->dateTime()
            ->sortable()
            ->toggleable()
            ->visible(config('eclipse.email_verification'))
            ->width(150);

        $columns[] = Tables\Columns\TextColumn::make('global_roles')
            ->label('Global Roles')
            ->translateLabel()
            ->badge()
            ->getStateUsing(
                fn (User $record): Collection => $record
                    ->roles()
                    ->whereNull('roles.'.config('permission.column_names.team_foreign_key'))
                    ->pluck('name')
                    ->map(fn ($roleName) => Str::headline($roleName))
            )
            ->sortable(false)
            ->placeholder('No global roles')
            ->toggleable();

        $columns[] = Tables\Columns\TextColumn::make('site_roles')
            ->label('Site Roles (current)')
            ->translateLabel()
            ->badge()
            ->color('warning')
            ->getStateUsing(function (User $record) {
                if (! Filament::getTenant()) {
                    return 'No site context';
                }

                return $record->roles()
                    ->where('roles.'.config('permission.column_names.team_foreign_key'), Filament::getTenant()->id)
                    ->pluck('name')
                    ->map(fn ($roleName) => Str::headline($roleName));
            })
            ->sortable(false)
            ->placeholder('No site roles')
            ->toggleable();
        $columns[] = Tables\Columns\TextColumn::make('country.name')
            ->badge();

        $columns[] = Tables\Columns\TextColumn::make('date_of_birth')
            ->date('M d, Y');

        $columns[] = Tables\Columns\TextColumn::make('created_at')
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true)
            ->width(150);

        $columns[] = Tables\Columns\TextColumn::make('updated_at')
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true)
            ->width(150);

        $filters = [
            Tables\Filters\TernaryFilter::make('email_verified_at')
                ->label('Email verification')
                ->nullable()
                ->placeholder('All users')
                ->trueLabel('Verified')
                ->falseLabel('Not verified')
                ->queries(
                    true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                    false: fn (Builder $query) => $query->whereNull('email_verified_at'),
                    blank: fn (Builder $query) => $query,
                )
                ->visible(config('eclipse.email_verification')),
            Tables\Filters\SelectFilter::make('country_id')
                ->label('Country')
                ->multiple()
                ->relationship('country', 'name', fn (Builder $query): Builder => $query->distinct())
                ->preload()
                ->optionsLimit(20),
            Tables\Filters\QueryBuilder::make()
                ->constraints([
                    TextConstraint::make('first_name')
                        ->label('First name'),
                    TextConstraint::make('last_name')
                        ->label('Last name'),
                    TextConstraint::make('name')
                        ->label('Full name'),
                    TextConstraint::make('last_login_at')
                        ->label('Last login Date'),
                    TextConstraint::make('login_count')
                        ->label('Total Logins'),
                ]),
            Tables\Filters\TrashedFilter::make(),
        ];

        return $table
            ->columns($columns)
            ->filters(self::getTableFilters())
            ->filtersFormWidth(MaxWidth::Large)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Impersonate::make()
                        ->grouped()
                        ->redirectTo(route('filament.admin.tenant')),
                    Tables\Actions\DeleteAction::make()
                        ->authorize(fn (User $record) => auth()->user()->can('delete_user') && auth()->id() !== $record->id)
                        ->requiresConfirmation(),
                    Tables\Actions\RestoreAction::make()
                        ->visible(fn (User $user) => $user->trashed() && auth()->user()->can('restore_user'))
                        ->requiresConfirmation(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->before(function ($records, Tables\Actions\DeleteBulkAction $action) {
                        $user_id = auth()->id();
                        $ids = $records->pluck('id')->toArray();
                        if (in_array($user_id, $ids)) {
                            Notification::make()
                                ->title('Error')
                                ->body('You cannot delete your own user account!')
                                ->status('danger')
                                ->send();

                            $action->cancel();
                        }
                    }),
                ]),
                ExportBulkAction::make()->exports([
                    TableExport::make('table'),
                ]),
            ]);
    }

    private static function getTableFilters(): array
    {
        return [
            Tables\Filters\TernaryFilter::make('user_visibility')
                ->label('Show Users From')
                ->placeholder(__('Current site (default)'))
                ->trueLabel(__('All accessible sites'))
                ->falseLabel(__('Current site only'))
                ->queries(
                    true: function (Builder $query): void {},
                    false: function (Builder $query): void {
                        if (Filament::getTenant()) {
                            $query->whereHas('sites', function ($subQuery) {
                                $subQuery->where('sites.id', Filament::getTenant()->id);
                            });
                        }
                    },
                    blank: function (Builder $query): void {
                        if (Filament::getTenant()) {
                            $query->whereHas('sites', function ($subQuery) {
                                $subQuery->where('sites.id', Filament::getTenant()->id);
                            });
                        }
                    }
                ),

            Tables\Filters\SelectFilter::make('global_roles')
                ->label('Global Roles')
                ->relationship('roles', 'name', function (Builder $query): void {
                    $query
                        ->whereNull('roles.'.config('permission.column_names.team_foreign_key'));
                })
                ->multiple()
                ->searchable()
                ->preload(),

            Tables\Filters\SelectFilter::make('site_roles')
                ->label('Site Roles')
                ->relationship('roles', 'name', function (Builder $query): void {
                    if (Filament::getTenant()) {
                        $query->where('roles.'.config('permission.column_names.team_foreign_key'), Filament::getTenant()->id);
                    }
                })
                ->multiple()
                ->searchable()
                ->preload()
                ->visible(fn () => Filament::getTenant() !== null),

            Tables\Filters\TernaryFilter::make('email_verified_at')
                ->label('Email verification')
                ->nullable()
                ->placeholder('All users')
                ->trueLabel('Verified')
                ->falseLabel('Not verified')
                ->queries(
                    true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                    false: fn (Builder $query) => $query->whereNull('email_verified_at'),
                    blank: fn (Builder $query) => $query,
                )
                ->visible(config('eclipse.email_verification')),
            Tables\Filters\QueryBuilder::make()
                ->constraints([
                    TextConstraint::make('first_name')
                        ->label('First name'),
                    TextConstraint::make('last_name')
                        ->label('Last name'),
                    TextConstraint::make('name')
                        ->label('Full name'),
                    TextConstraint::make('last_login_at')
                        ->label('Last login Date'),
                    TextConstraint::make('login_count')
                        ->label('Total Logins'),
                ]),

            Tables\Filters\TrashedFilter::make(),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make()
                ->columns(2)
                ->compact()
                ->schema([
                    TextEntry::make('created_at')
                        ->dateTime(),
                    TextEntry::make('updated_at')
                        ->dateTime(),
                ]),
            Section::make('Personal information')
                ->columns(4)
                ->schema([
                    SpatieMediaLibraryImageEntry::make('avatar')
                        ->collection('avatars')
                        ->defaultImageUrl(fn (User $user) => 'https://ui-avatars.com/api/?name='.urlencode($user->name))
                        ->circular(),
                    Group::make()
                        ->schema([
                            TextEntry::make('name')
                                ->label('Full name'),
                            TextEntry::make('email')
                                ->icon(config('eclipse.email_verification') ? fn (User $user) => $user->email_verified_at ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle' : null)
                                ->iconColor(fn (User $user) => $user->email_verified_at ? Color::Green : Color::Red),
                        ]),
                    Group::make()
                        ->schema([
                            TextEntry::make('phone_number')->placeholder('-'),
                            TextEntry::make('country.name')
                                ->badge()
                                ->placeholder('-'),
                        ]),
                    TextEntry::make('date_of_birth')->date('M d, Y')->placeholder('-'),
                ]),
            Section::make()
                ->compact()
                ->columns(2)
                ->schema([
                    TextEntry::make('sites')
                        ->label('Accessible Sites')
                        ->weight(FontWeight::Medium)
                        ->listWithLineBreaks()
                        ->placeholder(__('No sites accessible'))
                        ->formatStateUsing(fn ($state) => "✓ {$state->name} ({$state->domain})"),

                    TextEntry::make('global_roles')
                        ->label('Global Roles')
                        ->weight(FontWeight::Medium)
                        ->listWithLineBreaks()
                        ->placeholder(__('No global roles assigned'))
                        ->getStateUsing(function ($record): Collection {
                            if (! $record) {
                                return collect();
                            }

                            $globalRoles = DB::table('model_has_roles')
                                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                                ->where('model_has_roles.model_id', $record->id)
                                ->where('model_has_roles.model_type', get_class($record))
                                ->where('model_has_roles.is_global', true)
                                ->pluck('roles.name');

                            return $globalRoles->map(fn ($name) => '✓ '.Str::headline($name));
                        }),

                    TextEntry::make('site_roles_breakdown')
                        ->label('Site Roles')
                        ->columnSpanFull()
                        ->getStateUsing(function ($record): string {
                            if (! $record) {
                                return 'No site roles assigned';
                            }

                            $breakdown = [];

                            foreach (Site::all() as $site) {
                                $roles = DB::table('model_has_roles')
                                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                                    ->where('model_has_roles.model_id', $record->id)
                                    ->where('model_has_roles.model_type', get_class($record))
                                    ->where(function ($query) use ($site) {
                                        $query->where('model_has_roles.is_global', true)
                                            ->orWhere(function ($q) use ($site) {
                                                $q->where('model_has_roles.is_global', false)
                                                    ->where('model_has_roles.'.config('permission.column_names.team_foreign_key'), $site->id);
                                            });
                                    })
                                    ->pluck('roles.name')
                                    ->unique();

                                if ($roles->isNotEmpty()) {
                                    $roleList = $roles->map(fn ($name) => '✓ '.Str::headline($name))->join(', ');
                                    $breakdown[] = "<strong>{$site->name}:</strong> {$roleList}";
                                }
                            }

                            return $breakdown ? implode('<br>', $breakdown) : 'No site access';
                        })
                        ->formatStateUsing(fn ($state) => new HtmlString($state)),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Resources\UserResource\Pages\ListUsers::route('/'),
            'create' => Resources\UserResource\Pages\CreateUser::route('/create'),
            'view' => Resources\UserResource\Pages\ViewUser::route('/{record}'),
            'edit' => Resources\UserResource\Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getFirstNameFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('first_name')
            ->label('First name')
            ->required()
            ->maxLength(255);
    }

    public static function getLastNameFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('last_name')
            ->label('Last name')
            ->required()
            ->maxLength(255);
    }

    public static function getEmailFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('email')
            ->email()
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'first_name',
            'last_name',
            'email',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }

    private static function getSites(): Collection
    {
        return Site::get();
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'delete_any',
            'restore',
            'restore_any',
            'force_delete',
            'force_delete_any',
            'impersonate',
        ];
    }
}
