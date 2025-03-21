<?php

namespace Eclipse\Core\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Eclipse\Core\Filament\Resources;
use Eclipse\Core\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Users';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'first_name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\SpatieMediaLibraryFileUpload::make('avatar')
                ->collection('avatars')
                ->avatar()
                ->imageEditor()
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
                ->label(fn (string $context): string => $context === 'create' ? 'Password' : 'Set new password'),
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

        $columns[] = Tables\Columns\TextColumn::make('email_verified_at')
            ->label('Verified email')
            ->placeholder('Not verified')
            ->dateTime()
            ->sortable()
            ->toggleable()
            ->visible(config('eclipse.email_verification'))
            ->width(150);

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
            ->filters($filters)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
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
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make()
                ->columns(2)
                ->schema([
                    TextEntry::make('created_at')
                        ->dateTime(),
                    TextEntry::make('updated_at')
                        ->dateTime(),
                ]),
            Section::make('Personal information')
                ->columns(3)
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
        ];
    }
}
