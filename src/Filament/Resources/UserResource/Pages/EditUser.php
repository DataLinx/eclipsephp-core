<?php

namespace Eclipse\Core\Filament\Resources\UserResource\Pages;

use Eclipse\Core\Filament\Resources\UserResource;
use Eclipse\Core\Models\Site;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = static::getModel()::create(collect($data)->except($this->getRoleFieldNames())->toArray());
        $this->saveUserRoles($record, $data);

        return $record;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update(collect($data)->except($this->getRoleFieldNames())->toArray());
        $this->saveUserRoles($record, $data);

        return $record;
    }

    protected function getRoleFieldNames(): array
    {
        $siteFields = Site::all()->map(fn ($site) => "site_{$site->id}")->toArray();

        return array_merge(['global_roles'], $siteFields);
    }

    protected function saveUserRoles(Model $record, array $data): void
    {
        DB::table('model_has_roles')
            ->where('model_id', $record->id)
            ->where('model_type', get_class($record))
            ->delete();

        $globalRoleIds = ! empty($data['global_roles']) ? collect($data['global_roles'])->map(fn ($id) => (int) $id)->toArray() : [];

        if (! empty($globalRoleIds)) {
            foreach ($globalRoleIds as $roleId) {
                DB::table('model_has_roles')->insert([
                    'model_id' => $record->id,
                    'model_type' => get_class($record),
                    'role_id' => $roleId,
                    config('permission.column_names.team_foreign_key') => getPermissionsTeamId(),
                    'is_global' => true,
                ]);
            }
        }

        foreach (Site::all() as $site) {
            if (! empty($data["site_{$site->id}"])) {
                foreach ($data["site_{$site->id}"] as $roleId) {
                    $roleId = (int) $roleId;

                    if (! in_array($roleId, $globalRoleIds)) {
                        DB::table('model_has_roles')->insert([
                            'model_id' => $record->id,
                            'model_type' => get_class($record),
                            'role_id' => $roleId,
                            config('permission.column_names.team_foreign_key') => $site->id,
                            'is_global' => false,
                        ]);
                    }
                }
            }
        }
    }
}
