<?php

namespace Eclipse\Core\Filament\Resources;

use Eclipse\Core\Filament\Resources;
use Eclipse\Core\Models\Locale;
use Eclipse\Core\Models\Scopes\ActiveScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class LocaleResource extends Resource
{
    protected static ?string $model = Locale::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    public static function form(Form $form): Form
    {
        $schema = [
            TextInput::make('id')
                ->label(__('core::locale.id'))
                ->maxLength(2)
                ->disabled(function (?Locale $record): bool {
                    return ! is_null($record);
                })
                ->required()
                ->unique(table: Locale::class, ignoreRecord: true)
                ->helperText(new HtmlString(fstr(__('core::locale.id_help'))->parsePlaceholders(['link' => '<a href="https://en.wikipedia.org/wiki/List_of_ISO_639_language_codes#Table" target="_blank" class="font-bold">'.__('core::locale.id_help_values').'</a>']))),
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label(__('core::locale.name')),
            TextInput::make('native_name')
                ->required()
                ->maxLength(255)
                ->label(__('core::locale.native_name')),
        ];

        $locales = shell_exec('locale -a');

        if (! empty($locales)) {
            $options = explode("\n", trim($locales));

            $schema[] = Select::make('system_locale')
                ->required()
                ->options(array_combine($options, $options))
                ->searchable()
                ->label(__('core::locale.system_locale'));
        } else {
            $schema[] = TextInput::make('system_locale')
                ->required()
                ->maxLength(255)
                ->label(__('core::locale.system_locale'));
        }

        $helper_text = new HtmlString(fstr(__('core::locale.datetime_format_help'))->parsePlaceholders(['link' => '<a href="https://www.php.net/manual/en/datetime.format.php" target="_blank" class="font-bold">PHP DateTime Format</a>']));

        $schema[] = TextInput::make('datetime_format')
            ->required()
            ->maxLength(255)
            ->helperText($helper_text)
            ->label(__('core::locale.datetime_format'));

        $schema[] = TextInput::make('date_format')
            ->required()
            ->maxLength(255)
            ->helperText($helper_text)
            ->label(__('core::locale.date_format'));

        $schema[] = TextInput::make('time_format')
            ->required()
            ->maxLength(255)
            ->helperText($helper_text)
            ->label(__('core::locale.time_format'));

        return $form->schema($schema);
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
                    ->label(__('core::locale.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('native_name')
                    ->label(__('core::locale.native_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('system_locale')
                    ->label(__('core::locale.system_locale'))
                    ->sortable()
                    ->formatStateUsing(fn (?string $state = null): HtmlString => new HtmlString("<code>$state</code>")),
                Tables\Columns\TextColumn::make('datetime_format')
                    ->formatStateUsing(fn (?string $state = null): HtmlString => new HtmlString("<code>$state</code>"))
                    ->label(__('core::locale.datetime_format')),
                Tables\Columns\TextColumn::make('date_format')
                    ->formatStateUsing(fn (?string $state = null): HtmlString => new HtmlString("<code>$state</code>"))
                    ->label(__('core::locale.date_format')),
                Tables\Columns\TextColumn::make('time_format')
                    ->formatStateUsing(fn (?string $state = null): HtmlString => new HtmlString("<code>$state</code>"))
                    ->label(__('core::locale.time_format')),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label(__('core::locale.is_active'))
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_available_in_panel')
                    ->label(__('core::locale.is_available_in_panel'))
                    ->disabled(function (Locale $record): bool {
                        return ! $record->is_active;
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(__('core::locale.actions.edit')),
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
        return __('core::locale.nav.item');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Configuration';
    }

    public static function getPluralModelLabel(): string
    {
        return __('core::locale.nav.item');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                ActiveScope::class,
            ]);
    }
}
