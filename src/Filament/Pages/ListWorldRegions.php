<?php

namespace Eclipse\Core\Filament\Pages;

use Eclipse\Core\Filament\Resources\WorldRegionResource;
use Filament\Resources\Pages\ListRecords;

class ListWorldRegions extends ListRecords
{
    protected static string $resource = WorldRegionResource::class;
}
