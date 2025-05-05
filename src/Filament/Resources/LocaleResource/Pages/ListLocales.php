<?php

namespace Eclipse\Core\Filament\Resources\LocaleResource\Pages;

use Eclipse\Core\Filament\Resources\LocaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListLocales extends ListRecords
{
    protected static string $resource = LocaleResource::class;

    protected ?string $maxContentWidth = MaxWidth::Full->value;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__('eclipse::locale.actions.create.label')),
        ];
    }
}
