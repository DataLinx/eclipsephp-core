<?php

namespace Eclipse\Core\Filament\Resources\UserResource\Pages;

use Eclipse\Core\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Nben\FilamentRecordNav\Actions\NextRecordAction;
use Nben\FilamentRecordNav\Actions\PreviousRecordAction;
use Nben\FilamentRecordNav\Concerns\WithRecordNavigation;

class EditUser extends EditRecord
{
    use WithRecordNavigation;

    protected static string $resource = UserResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return __('Edit User');
    }

    protected function getHeaderActions(): array
    {
        return [
            PreviousRecordAction::make(),
            NextRecordAction::make(),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
