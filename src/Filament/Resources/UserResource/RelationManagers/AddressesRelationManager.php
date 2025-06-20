<?php

declare(strict_types=1);

namespace Eclipse\Core\Filament\Resources\UserResource\RelationManagers;

use Eclipse\Core\Enums\AddressType;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public static function getAddressForm(): array
    {
        return [
            Forms\Components\CheckboxList::make('type')
                ->live()
                ->default([AddressType::DEFAULT_ADDRESS->value])
                ->options(AddressType::class)
                ->columns(2),
            Forms\Components\TextInput::make('recipient')
                ->maxLength(100)
                ->label('Full name')
                ->required(),
            Forms\Components\TextInput::make('company_name')
                ->visible(fn (Get $get): bool => in_array(AddressType::COMPANY_ADDRESS->value, $get('type') ?? []))
                ->required()
                ->maxLength(100),
            Forms\Components\TextInput::make('company_vat_id')
                ->visible(fn (Get $get): bool => in_array(AddressType::COMPANY_ADDRESS->value, $get('type') ?? []))
                ->label('Company VAT ID')
                ->maxLength(50),
            Forms\Components\Repeater::make('street_address')
                ->minItems(1)
                ->maxItems(3)
                ->required()
                ->simple(
                    Forms\Components\TextInput::make('street_address')
                        ->maxLength(255)
                        ->required()
                )
                ->addActionLabel(__('Add address line')),
            Forms\Components\Split::make([
                Forms\Components\TextInput::make('postal_code')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('city')
                    ->required()
                    ->maxLength(100),
            ]),
            Forms\Components\Select::make('country_id')
                ->required()
                ->relationship('country', 'name'),
        ];
    }

    public static function getAddressInfolist(): array
    {
        return [
            Infolists\Components\Grid::make()->schema([
                Infolists\Components\TextEntry::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => self::formatAddressTypeLabels($state)),
                Infolists\Components\TextEntry::make('recipient'),
                Infolists\Components\TextEntry::make('company_name')
                    ->visible(fn ($record): bool => self::hasCompanyAddress($record->type)),
                Infolists\Components\TextEntry::make('company_vat_id')
                    ->visible(fn ($record): bool => self::hasCompanyAddress($record->type))
                    ->default('-')
                    ->label('Company VAT ID'),
                Infolists\Components\TextEntry::make('street_address')
                    ->listWithLineBreaks(),
                Infolists\Components\TextEntry::make('country')
                    ->formatStateUsing(fn ($state) => "{$state->name} {$state->flag}"),
                Infolists\Components\Split::make([
                    Infolists\Components\TextEntry::make('postal_code')
                        ->badge()
                        ->color('warning'),
                    Infolists\Components\TextEntry::make('city'),
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
                Tables\Columns\TextColumn::make('recipient')
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
                Tables\Columns\TextColumn::make('company_vat_id')
                    ->placeholder('-')
                    ->label('Company VAT ID'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->placeholder('-')
                    ->formatStateUsing(fn ($state) => self::formatAddressTypeLabels($state)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(AddressType::class)
                    ->query(function (Builder $query, array $data): Builder {
                        if (filled($data['value'])) {
                            return $query->whereJsonContains('type', $data['value']);
                        }

                        return $query;
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist(self::getAddressInfolist()),
                Tables\Actions\EditAction::make()
                    ->form(self::getAddressForm()),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add new address')
                    ->icon('heroicon-o-plus-circle')
                    ->form(self::getAddressForm()),
            ]);
    }
}
