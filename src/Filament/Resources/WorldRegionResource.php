<?php

namespace Eclipse\Core\Filament\Resources;

use Eclipse\Core\Filament\Pages\CreateWorldRegion;
use Eclipse\Core\Filament\Pages\EditWorldRegion;
use Eclipse\Core\Filament\Pages\ListWorldRegions;
use Eclipse\Core\Models\WorldRegion;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class WorldRegionResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = WorldRegion::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-europe-africa';
    protected static ?string $navigationGroup = 'World';

    public static function getPermissionPrefixes(): array
    {
        return ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('code')->nullable()->maxLength(20),
            Forms\Components\Toggle::make('is_special')->label('Special region'),
            Forms\Components\Select::make('parent_id')
                ->relationship('parent', 'name')
                ->label('Parent Region')
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('code')->sortable()->searchable(),
                Tables\Columns\IconColumn::make('is_special')->boolean(),
                Tables\Columns\TextColumn::make('parent.name')->label('Parent'),
            ])
            ->filters([
                TrashedFilter::make()
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorldRegions::route('/'),
            'create' => CreateWorldRegion::route('/create'),
            'edit' => EditWorldRegion::route('/{record}/edit'),
        ];
    }
}
