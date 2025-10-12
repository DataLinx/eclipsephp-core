<?php

declare(strict_types=1);

namespace Eclipse\Core\Policies\User;

use Eclipse\Core\Models\User\Role;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_role');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('view_role');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_role');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('update_role');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('delete_role');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any_role');
    }
}
