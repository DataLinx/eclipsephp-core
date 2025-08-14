<?php

namespace Eclipse\Core\Traits;

use Eclipse\Core\Models\User\Role;
use Illuminate\Support\Collection;

trait HasSiteRoles
{
    public function assignGlobalRole($role): self
    {
        $roleName = $this->convertToRoleName($role);

        $currentTeamId = getPermissionsTeamId();
        setPermissionsTeamId(null);
        $this->assignRole($roleName);
        setPermissionsTeamId($currentTeamId);
        $this->load('roles');

        return $this;
    }

    public function assignSiteRole($role, int $siteId): self
    {
        $roleName = $this->convertToRoleName($role);

        $currentTeamId = getPermissionsTeamId();
        setPermissionsTeamId($siteId);
        $this->assignRole($roleName);
        setPermissionsTeamId($currentTeamId);
        $this->load('roles');

        return $this;
    }

    public function syncGlobalRoles(array $roles): self
    {
        $roleNames = $this->convertToRoleNames($roles);

        $currentTeamId = getPermissionsTeamId();
        setPermissionsTeamId(null);
        $this->syncRoles($roleNames);
        setPermissionsTeamId($currentTeamId);
        $this->load('roles');

        return $this;
    }

    public function syncSiteRoles(array $roles, int $siteId): self
    {
        $roleNames = $this->convertToRoleNames($roles);

        $currentTeamId = getPermissionsTeamId();
        setPermissionsTeamId($siteId);
        $this->syncRoles($roleNames);
        setPermissionsTeamId($currentTeamId);
        $this->load('roles');

        return $this;
    }

    private function convertToRoleName($role)
    {
        if (is_numeric($role)) {
            return \Eclipse\Core\Models\User\Role::findOrFail($role)->name;
        }

        return $role;
    }

    private function convertToRoleNames(array $roles): array
    {
        if (empty($roles)) {
            return [];
        }

        if (is_numeric($roles[0])) {
            return Role::whereIn('id', $roles)
                ->pluck('name')
                ->toArray();
        }

        return $roles;
    }

    public function globalRoles(): Collection
    {
        $currentTeamId = getPermissionsTeamId();
        setPermissionsTeamId(null);
        $roles = $this->roles()->get();
        setPermissionsTeamId($currentTeamId);

        return $roles;
    }

    public function siteRoles(int $siteId): Collection
    {
        $currentTeamId = getPermissionsTeamId();
        setPermissionsTeamId($siteId);
        $roles = $this->roles()->get();
        setPermissionsTeamId($currentTeamId);

        return $roles;
    }

    public function hasGlobalRole($role): bool
    {
        return $this->globalRoles()->contains('name', $role);
    }

    public function hasSiteRole($role, int $siteId): bool
    {
        return $this->siteRoles($siteId)->contains('name', $role);
    }
}
