<?php

namespace Eclipse\Core\Filament\Pages;

use Eclipse\Core\Filament\Resources\CountryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCountry extends CreateRecord
{
    protected static string $resource = CountryResource::class;
}
