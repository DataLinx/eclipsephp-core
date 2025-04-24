<?php

namespace Eclipse\Core\Filament\Pages;

use Eclipse\Core\Filament\Resources\CountryResource;
use Filament\Resources\Pages\ListRecords;

class ListCountries extends ListRecords
{
    protected static string $resource = CountryResource::class;
}
