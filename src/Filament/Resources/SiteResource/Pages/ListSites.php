<?php

namespace Eclipse\Core\Filament\Resources\SiteResource\Pages;

use Eclipse\Core\Filament\Resources\SiteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListSites extends ListRecords
{
    protected static string $resource = SiteResource::class;

    protected Width|string|null $maxContentWidth = Width::Full->value;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
