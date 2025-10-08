<?php

namespace Eclipse\Core\Filament\Resources\UserResource\Pages;

use Eclipse\Core\Filament\Resources\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected Width|string|null $maxContentWidth = Width::Full->value;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
