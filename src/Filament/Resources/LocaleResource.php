<?php

namespace Eclipse\Core\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Eclipse\Core\Filament\Resources;
use Eclipse\Core\Models\Locale;
use Eclipse\Core\Models\Scopes\ActiveScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class LocaleResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Locale::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    public static function form(Form $form): Form
    {
        $basic = [
            TextInput::make('id')
                ->label(__('eclipse::locale.id'))
                ->maxLength(2)
                ->disabled(function (?Locale $record): bool {
                    return ! is_null($record);
                })
                ->required()
                ->unique(table: Locale::class, ignoreRecord: true)
                ->helperText(new HtmlString(fstr(__('eclipse::locale.id_help'))->parsePlaceholders(['link' => '<a href="https://en.wikipedia.org/wiki/List_of_ISO_639_language_codes#Table" target="_blank" class="font-bold">'.__('eclipse::locale.id_help_values').'</a>']))),
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label(__('eclipse::locale.name')),
            TextInput::make('native_name')
                ->required()
                ->maxLength(255)
                ->label(__('eclipse::locale.native_name')),
        ];

        $locales = shell_exec('locale -a');

        if (! empty($locales)) {
            $options = explode("\n", trim($locales));

            $basic[] = Select::make('system_locale')
                ->required()
                ->options(array_combine($options, $options))
                ->searchable()
                ->label(__('eclipse::locale.system_locale'));
        } else {
            $basic[] = TextInput::make('system_locale')
                ->required()
                ->maxLength(255)
                ->label(__('eclipse::locale.system_locale'));
        }

        return $form->schema([
            Section::make(__('eclipse::locale.sections.basic'))
                ->schema($basic),
            Section::make(__('eclipse::locale.sections.datetime_formats'))
                ->description(new HtmlString(fstr(__('eclipse::locale.datetime_format_help'))->parsePlaceholders(['link' => '<a href="https://www.php.net/manual/en/datetime.format.php" target="_blank" class="font-bold">PHP DateTime Format</a>'])))
                ->schema([
                    TextInput::make('datetime_format')
                        ->required()
                        ->maxLength(255)
                        ->label(__('eclipse::locale.datetime_format')),

                    TextInput::make('date_format')
                        ->required()
                        ->maxLength(255)
                        ->label(__('eclipse::locale.date_format')),

                    TextInput::make('time_format')
                        ->required()
                        ->maxLength(255)
                        ->label(__('eclipse::locale.time_format')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('eclipse::locale.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('native_name')
                    ->label(__('eclipse::locale.native_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('system_locale')
                    ->label(__('eclipse::locale.system_locale'))
                    ->sortable()
                    ->formatStateUsing(fn (?string $state = null): HtmlString => new HtmlString("<code>$state</code>")),
                Tables\Columns\TextColumn::make('datetime_format')
                    ->formatStateUsing(fn (?string $state = null): HtmlString => new HtmlString("<code>$state</code>"))
                    ->label(__('eclipse::locale.datetime_format')),
                Tables\Columns\TextColumn::make('date_format')
                    ->formatStateUsing(fn (?string $state = null): HtmlString => new HtmlString("<code>$state</code>"))
                    ->label(__('eclipse::locale.date_format')),
                Tables\Columns\TextColumn::make('time_format')
                    ->formatStateUsing(fn (?string $state = null): HtmlString => new HtmlString("<code>$state</code>"))
                    ->label(__('eclipse::locale.time_format')),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label(__('eclipse::locale.is_active'))
                    ->sortable()
                    ->disabled(fn (Locale $record): bool => ! auth()->user()->can('update', $record)),
                Tables\Columns\ToggleColumn::make('is_available_in_panel')
                    ->label(__('eclipse::locale.is_available_in_panel'))
                    ->disabled(function (Locale $record): bool {
                        return ! $record->is_active or ! auth()->user()->can('update', $record);
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(__('eclipse::locale.actions.edit.label')),
                ActionGroup::make([
                    DeleteAction::make()
                        ->label(__('eclipse-world::countries.actions.delete.label'))
                        ->modalHeading(__('eclipse-world::countries.actions.delete.heading')),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Resources\LocaleResource\Pages\ListLocales::route('/'),
            'create' => Resources\LocaleResource\Pages\CreateLocale::route('/create'),
            'edit' => Resources\LocaleResource\Pages\EditLocale::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('eclipse::locale.nav.item');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('eclipse-common::nav.configuration');
    }

    public static function getPluralModelLabel(): string
    {
        return __('eclipse::locale.nav.item');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                ActiveScope::class,
            ]);
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
}
