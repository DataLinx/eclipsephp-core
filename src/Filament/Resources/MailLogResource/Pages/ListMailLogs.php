<?php

namespace Eclipse\Core\Filament\Resources\MailLogResource\Pages;

use Eclipse\Core\Filament\Resources\MailLogResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListMailLogs extends ListRecords
{
    protected static string $resource = MailLogResource::class;

    protected ?string $maxContentWidth = MaxWidth::Full->value;

    protected function getHeaderActions(): array
    {
        return [
            // No create action needed for mail logs
        ];
    }
}
