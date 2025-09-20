<?php

namespace Eclipse\Core\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Eclipse\Core\Filament\Actions\SendEmailTableAction;
use Eclipse\Core\Filament\Exports\TableExport;
use Eclipse\Core\Filament\Resources\UserResource\Pages\CreateUser;
use Eclipse\Core\Filament\Resources\UserResource\Pages\EditUser;
use Eclipse\Core\Filament\Resources\UserResource\Pages\ListUsers;
use Eclipse\Core\Filament\Resources\UserResource\Pages\ViewUser;
use Eclipse\Core\Filament\Resources\UserResource\RelationManagers\AddressesRelationManager;
use Eclipse\Core\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Users';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SpatieMediaLibraryFileUpload::make('avatar')
                ->collection('avatars')
                ->avatar()
                ->imageEditor()
                ->maxSize(1024 * 2),
            self::getFirstNameFormComponent(),
            self::getLastNameFormComponent(),
            self::getEmailFormComponent(),
            TextInput::make('phone_number')
                ->label('Phone')
                ->tel(),
            DateTimePicker::make('email_verified_at')
                ->visible(config('eclipse.email_verification'))
                ->disabled(),
            TextInput::make('password')
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
            Select::make('country_id')
                ->relationship('country', 'name')
                ->preload()
                ->optionsLimit(20)
                ->searchable(),
            DatePicker::make('date_of_birth')
                ->native(false)
                ->minDate(now()->subYears(80))
                ->maxDate(now()),
            Select::make('roles')
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
            TextColumn::make('first_name')
                ->searchable()
                ->sortable()
                ->toggleable(),
            TextColumn::make('last_name')
                ->searchable()
                ->sortable()
                ->toggleable(),
            TextColumn::make('name')
                ->label('Full name')
                ->searchable()
                ->sortable()
                ->toggleable(),
            TextColumn::make('last_login_at')
                ->label('Last login')
                ->dateTime()
                ->sortable()
                ->toggleable(),
            TextColumn::make('login_count')
                ->label('Total Logins')
                ->sortable()
                ->numeric()
                ->formatStateUsing(fn (?int $state) => $state ?? 0),
        ];

        if (config('eclipse.email_verification')) {
            $columns[] = TextColumn::make('email')
                ->searchable()
                ->sortable()
                ->width(150)
                ->icon(fn (User $user) => $user->email_verified_at ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle')
                ->iconColor(fn (User $user) => $user->email_verified_at ? Color::Green : Color::Red)
                ->tooltip(fn (User $user) => $user->email_verified_at ? 'Verified' : 'Not verified');
        } else {
            $columns[] = TextColumn::make('email')
                ->searchable()
                ->sortable()
                ->width(150);
        }

        $columns[] = TextColumn::make('phone_number')
            ->label('Phone');

        $columns[] = TextColumn::make('email_verified_at')
            ->label('Verified email')
            ->placeholder('Not verified')
            ->dateTime()
            ->sortable()
            ->toggleable()
            ->visible(config('eclipse.email_verification'))
            ->width(150);

        $columns[] = TextColumn::make('country.name')
            ->badge();

        $columns[] = TextColumn::make('date_of_birth')
            ->date('M d, Y');

        $columns[] = TextColumn::make('created_at')
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true)
            ->width(150);

        $columns[] = TextColumn::make('updated_at')
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true)
            ->width(150);

        $filters = [
            TernaryFilter::make('email_verified_at')
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
            SelectFilter::make('country_id')
                ->label('Country')
                ->multiple()
                ->relationship('country', 'name', fn (Builder $query): Builder => $query->distinct())
                ->preload()
                ->optionsLimit(20),
            QueryBuilder::make()
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
            TrashedFilter::make(),
        ];

        return $table
            ->columns($columns)
            ->filters($filters)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    SendEmailTableAction::makeAction(),
                    Impersonate::make()
                        ->grouped()
                        ->redirectTo(route('filament.admin.tenant')),
                    DeleteAction::make()
                        ->authorize(fn (User $record) => auth()->user()->can('delete_user') && auth()->id() !== $record->id)
                        ->requiresConfirmation(),
                    RestoreAction::make()
                        ->visible(fn (User $user) => $user->trashed() && auth()->user()->can('restore_user'))
                        ->requiresConfirmation(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->before(function ($records, DeleteBulkAction $action) {
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

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
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
        ]);
    }

    public static function getRelations(): array
    {
        return [
            AddressesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getFirstNameFormComponent(): TextInput
    {
        return TextInput::make('first_name')
            ->label('First name')
            ->required()
            ->maxLength(255);
    }

    public static function getLastNameFormComponent(): TextInput
    {
        return TextInput::make('last_name')
            ->label('Last name')
            ->required()
            ->maxLength(255);
    }

    public static function getEmailFormComponent(): TextInput
    {
        return TextInput::make('email')
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
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
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
            'send_email',
        ];
    }
}
