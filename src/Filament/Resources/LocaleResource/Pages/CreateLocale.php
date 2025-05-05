<?php

namespace Eclipse\Core\Filament\Resources\LocaleResource\Pages;

use Eclipse\Core\Filament\Resources\LocaleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateLocale extends CreateRecord
{
    protected static string $resource = LocaleResource::class;

    public function getHeading(): string|Htmlable
    {
        return __('eclipse::locale.actions.create.heading');
    }
}
