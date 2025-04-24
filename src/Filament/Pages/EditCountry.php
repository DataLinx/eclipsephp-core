<?php

namespace Eclipse\Core\Filament\Pages;

use Eclipse\Core\Filament\Resources\CountryResource;
use Filament\Resources\Pages\EditRecord;

class EditCountry extends EditRecord
{
    protected static string $resource = CountryResource::class;
}
