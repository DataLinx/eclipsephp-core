<?php

namespace Eclipse\Core\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Eclipse\Core\Filament\Resources\SiteResource\Pages\CreateSite;
use Eclipse\Core\Filament\Resources\SiteResource\Pages\EditSite;
use Eclipse\Core\Filament\Resources\SiteResource\Pages\ListSites;
use Eclipse\Core\Models\Site;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SiteResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Site::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-globe-alt';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('domain')
                    ->label('Domain')
                    ->required()
                    ->maxLength(255),
                TextInput::make('name')
                    ->label('Site name')
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Is active'),
                Toggle::make('is_secure')
                    ->label('Secure site')
                    ->helperText('Use HTTPS for the site by default.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->width(50),
                TextColumn::make('name')
                    ->label('Site name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('domain')
                    ->label('Domain')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Is active')
                    ->width(100),
                ToggleColumn::make('is_secure')
                    ->label('Secure site')
                    ->width(100),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListSites::route('/'),
            'create' => CreateSite::route('/create'),
            'edit' => EditSite::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('eclipse-common::nav.configuration');
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return config('eclipse.multi_site');
    }
}
