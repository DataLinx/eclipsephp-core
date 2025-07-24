<?php

namespace Eclipse\Core\Filament\Resources\UserResource\Pages;

use Eclipse\Core\Filament\Actions\SendEmailAction;
use Eclipse\Core\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use STS\FilamentImpersonate\Pages\Actions\Impersonate;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return __('View User');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            SendEmailAction::make(),
            Impersonate::make()
                ->record($this->getRecord())
                ->redirectTo(route('filament.admin.tenant')),
        ];
    }
}
