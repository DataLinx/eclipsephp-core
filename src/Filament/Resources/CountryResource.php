<?php

namespace Eclipse\Core\Filament\Resources;

use Eclipse\Core\Filament\Pages\CreateCountry;
use Eclipse\Core\Filament\Pages\EditCountry;
use Eclipse\Core\Filament\Pages\ListCountries;
use Eclipse\Core\Models\WorldCountry;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class CountryResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = WorldCountry::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'World';

    protected static ?string $slug = 'countries';

    public static function getPermissionPrefixes(): array
    {
        return ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('code')
                ->required()
                ->maxLength(10),
            Forms\Components\Select::make('region_id')
                ->relationship('geoRegion', 'name')
                ->label('Geo Region')
                ->searchable()
                ->nullable(),
            Forms\Components\Select::make('specialRegions')
                ->multiple()
                ->relationship('specialRegions', 'name')
                ->label('Special Regions')
                ->searchable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('code')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('geoRegion.name')
                    ->label('Geo Region')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('specialRegions.name')
                    ->label('Special Regions')
                    ->badge()
                    ->separator(', ')
            ])
            ->filters([
                SelectFilter::make('region_id')
                    ->relationship('geoRegion', 'name')
                    ->label('Geo Region'),
                SelectFilter::make('specialRegions')
                    ->relationship('specialRegions', 'name')
                    ->label('Special Region'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCountries::route('/'),
            'create' => CreateCountry::route('/create'),
            'edit' => EditCountry::route('/{record}/edit'),
        ];
    }
}
