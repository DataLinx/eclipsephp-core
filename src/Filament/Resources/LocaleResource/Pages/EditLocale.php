<?php

namespace Eclipse\Core\Filament\Resources\LocaleResource\Pages;

use Eclipse\Core\Filament\Resources\LocaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLocale extends EditRecord
{
    protected static string $resource = LocaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
