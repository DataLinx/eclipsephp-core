<?php

namespace Eclipse\Core\Filament\Resources\SiteResource\Pages;

use Eclipse\Core\Filament\Resources\SiteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListSites extends ListRecords
{
    protected static string $resource = SiteResource::class;

    protected ?string $maxContentWidth = MaxWidth::Full->value;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
