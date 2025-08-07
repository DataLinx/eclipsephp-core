<?php

namespace Eclipse\Core\Filament\Resources\UserResource\Pages;

use Eclipse\Core\Filament\Resources\UserResource;
use Eclipse\Core\Filament\Resources\UserResource\Pages\Concerns\HandlesRoles;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use HandlesRoles;

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
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
