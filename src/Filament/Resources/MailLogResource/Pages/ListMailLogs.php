<?php

namespace Eclipse\Core\Filament\Resources\MailLogResource\Pages;

use Eclipse\Core\Filament\Resources\MailLogResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListMailLogs extends ListRecords
{
    protected static string $resource = MailLogResource::class;

    protected Width|string|null $maxContentWidth = Width::Full->value;

    protected function getHeaderActions(): array
    {
        return [
            // No create action needed for mail logs
        ];
    }
}
