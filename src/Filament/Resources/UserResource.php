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
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Users';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'first_name';

    public static function form(Form $form): Form
    {
        $schema = [
            Forms\Components\SpatieMediaLibraryFileUpload::make('avatar')
                ->collection('avatars')
                ->avatar()
                ->imageEditor()
                ->maxSize(1024 * 2),
            self::getFirstNameFormComponent(),
            self::getLastNameFormComponent(),
            self::getEmailFormComponent(),
        ];

        if (config('eclipse.email_verification')) {
            $schema[] = Forms\Components\DateTimePicker::make('email_verified_at')
                ->disabled();
        }

        $schema[] = Forms\Components\TextInput::make('password')
            ->password()
            ->revealable()
            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
            ->dehydrated(fn ($state) => filled($state))
            ->required(fn (string $context): bool => $context === 'create')
            ->label(fn (string $context): string => $context === 'create' ? 'Password' : 'Set new password');

        return $form->schema($schema);
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
            Tables\Columns\TextColumn::make('full_name')
                ->searchable(query: function (Builder $query, string $search): Builder {
                    return $query->whereRaw(User::getCompositeDefinition('full_name').' LIKE ?', ["%$search%"]);
                })
                ->sortable()
                ->toggleable(),
        ];

        if (config('eclipse.email_verification')) {
            $columns[] = Tables\Columns\TextColumn::make('email')
                ->searchable()
                ->sortable()
                ->width(150)
                ->icon(fn (User $user) => $user->email_verified_at ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle')
                ->iconColor(fn (User $user) => $user->email_verified_at ? Color::Green : Color::Red)
                ->tooltip(fn (User $user) => $user->email_verified_at ? 'Verified' : 'Not verified');

            $columns[] = Tables\Columns\TextColumn::make('email_verified_at')
                ->label('Verified email')
                ->placeholder('Not verified')
                ->dateTime()
                ->sortable()
                ->toggleable()
                ->width(150);
        } else {
            $columns[] = Tables\Columns\TextColumn::make('email')
                ->searchable()
                ->sortable()
                ->width(150);
        }

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

        $filters = [];

        if (config('eclipse.email_verification')) {
            $filters[] = Tables\Filters\TernaryFilter::make('email_verified_at')
                ->label('Email verification')
                ->nullable()
                ->placeholder('All users')
                ->trueLabel('Verified')
                ->falseLabel('Not verified')
                ->queries(
                    true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                    false: fn (Builder $query) => $query->whereNull('email_verified_at'),
                    blank: fn (Builder $query) => $query,
                );
        }

        $filters[] = Tables\Filters\QueryBuilder::make()
            ->constraints([
                TextConstraint::make('first_name'),
            ]);

        return $table
            ->columns($columns)
            ->filters($filters)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()->disabled(fn (User $user) => $user->id === auth()->user()->id),
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
                            TextEntry::make('full_name')
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

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
}
