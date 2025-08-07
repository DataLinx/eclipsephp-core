<?php

namespace Eclipse\Core\Filament\Resources\UserResource\Pages\Concerns;

use Eclipse\Core\Models\Site;
use Illuminate\Database\Eloquent\Model;

trait HandlesRoles
{
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = $this->removeRoleFields($data);

        return parent::mutateFormDataBeforeSave($data);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->removeRoleFields($data);

        return parent::mutateFormDataBeforeCreate($data);
    }

    protected function handleRecordUpdate($record, array $data): Model
    {
        $record = parent::handleRecordUpdate($record, $data);

        $formData = $this->form->getState();
        $this->saveRoles($formData);

        return $record;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        $formData = $this->form->getState();
        $this->saveRoles($formData);

        return $record;
    }

    protected function saveRoles(array $data): void
    {
        $user = $this->record ?? $this->getRecord();

        if (! $user) {
            return;
        }

        $user->syncGlobalRoles($data['global_roles'] ?? []);

        foreach (Site::all() as $site) {
            $siteRoles = $data["site_{$site->id}_roles"] ?? [];
            $user->syncSiteRoles($siteRoles, $site->id);
        }
    }

    protected function removeRoleFields(array $data): array
    {
        unset($data['global_roles']);

        foreach (Site::all() as $site) {
            unset($data["site_{$site->id}_roles"]);
        }

        return $data;
    }
}
