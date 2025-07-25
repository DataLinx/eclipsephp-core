<?php

namespace Eclipse\Core\Filament\Resources\MailLogResource\Pages;

use Eclipse\Core\Filament\Resources\MailLogResource;
use Filament\Resources\Pages\ListRecords;

class ListMailLogs extends ListRecords
{
    protected static string $resource = MailLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action needed for mail logs
        ];
    }
}
