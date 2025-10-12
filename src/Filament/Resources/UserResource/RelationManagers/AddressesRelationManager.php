<?php

declare(strict_types=1);

namespace Eclipse\Core\Filament\Resources\UserResource\RelationManagers;

use Eclipse\Core\Enums\AddressType;
use Eclipse\Core\Settings\EclipseSettings;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $isAddressBookEnabled = app(EclipseSettings::class)->address_book ?? false;

        return $isAddressBookEnabled;
    }

    public static function getAddressForm(): array
    {
        return [
            CheckboxList::make('type')
                ->live()
                ->default([AddressType::DEFAULT_ADDRESS->value])
                ->options(AddressType::class)
                ->columns(2),
            TextInput::make('recipient')
                ->maxLength(100)
                ->label('Full name')
                ->required(),
            TextInput::make('company_name')
                ->visible(fn (Get $get): bool => in_array(AddressType::COMPANY_ADDRESS->value, $get('type') ?? []))
                ->required()
                ->maxLength(100),
            TextInput::make('company_vat_id')
                ->visible(fn (Get $get): bool => in_array(AddressType::COMPANY_ADDRESS->value, $get('type') ?? []))
                ->label('Company VAT ID')
                ->maxLength(50),
            Repeater::make('street_address')
                ->minItems(1)
                ->maxItems(3)
                ->required()
                ->simple(
                    TextInput::make('street_address')
                        ->maxLength(255)
                        ->required()
                )
                ->addActionLabel(__('Add address line')),
            Flex::make([
                TextInput::make('postal_code')
                    ->required()
                    ->maxLength(50),
                TextInput::make('city')
                    ->required()
                    ->maxLength(100),
            ]),
            Select::make('country_id')
                ->required()
                ->relationship('country', 'name'),
        ];
    }

    public static function getAddressInfolist(): array
    {
        return [
            Grid::make()->schema([
                TextEntry::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => self::formatAddressTypeLabels($state)),
                TextEntry::make('recipient'),
                TextEntry::make('company_name')
                    ->visible(fn ($record): bool => self::hasCompanyAddress($record->type)),
                TextEntry::make('company_vat_id')
                    ->visible(fn ($record): bool => self::hasCompanyAddress($record->type))
                    ->default('-')
                    ->label('Company VAT ID'),
                TextEntry::make('street_address')
                    ->listWithLineBreaks(),
                TextEntry::make('country')
                    ->formatStateUsing(fn ($state) => "{$state->name} {$state->flag}"),
                Flex::make([
                    TextEntry::make('postal_code')
                        ->badge()
                        ->color('warning'),
                    TextEntry::make('city'),
                ])->columnSpanFull(),
            ]),
        ];
    }

    private static function hasCompanyAddress($types): bool
    {
        if (! is_array($types)) {
            return false;
        }

        return collect($types)->contains(function ($type) {
            return ($type instanceof AddressType && $type === AddressType::COMPANY_ADDRESS) ||
                $type === AddressType::COMPANY_ADDRESS->value;
        });
    }

    private static function formatAddressTypeLabels($state): string
    {
        if (is_array($state)) {
            return collect($state)->map(function ($type) {
                if (is_string($type)) {
                    return AddressType::from($type)->getLabel();
                }

                return $type->getLabel();
            })->join(', ');
        }

        if (is_string($state)) {
            return AddressType::from($state)->getLabel();
        }

        return $state->getLabel();
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['country']))
            ->columns([
                TextColumn::make('recipient')
                    ->weight(FontWeight::Bold)
                    ->description(function ($record) {
                        $recipient = [];

                        if ($record->company_name) {
                            $recipient[] = $record->company_name;
                        }

                        $recipient[] = implode('<br/>', $record->street_address);

                        $recipient[] = "{$record->country->flag} {$record->country->name}";

                        return new HtmlString(implode('<br/>', $recipient));
                    })
                    ->searchable(['recipient', 'company_name', 'street_address', 'country_id']),
                TextColumn::make('company_vat_id')
                    ->placeholder('-')
                    ->label('Company VAT ID'),
                TextColumn::make('type')
                    ->badge()
                    ->placeholder('-')
                    ->formatStateUsing(fn ($state) => self::formatAddressTypeLabels($state)),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(AddressType::class)
                    ->query(function (Builder $query, array $data): Builder {
                        if (filled($data['value'])) {
                            return $query->whereJsonContains('type', $data['value']);
                        }

                        return $query;
                    }),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->schema(self::getAddressInfolist()),
                EditAction::make()
                    ->schema(self::getAddressForm()),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add new address')
                    ->icon('heroicon-o-plus-circle')
                    ->schema(self::getAddressForm()),
            ]);
    }
}
