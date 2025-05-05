<?php

namespace Eclipse\Core\Filament\Resources\UserResource\Pages;

use Eclipse\Core\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected ?string $maxContentWidth = MaxWidth::Full->value;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
