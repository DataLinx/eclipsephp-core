<?php

namespace Eclipse\Core\Filament\Resources\LocaleResource\Pages;

use Eclipse\Core\Filament\Resources\LocaleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListLocales extends ListRecords
{
    protected static string $resource = LocaleResource::class;

    protected Width|string|null $maxContentWidth = Width::Full->value;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('eclipse::locale.actions.create.label')),
        ];
    }
}
