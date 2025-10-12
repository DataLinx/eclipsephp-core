<?php

namespace Eclipse\Core\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_user');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('view_user');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_user');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('update_user');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(AuthUser $authUser, AuthUser $user): bool
    {
        if ($authUser->id === $user->id) {
            return false;
        }

        return $authUser->can('delete_user');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any_user');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('restore_user');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_user');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_user');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_user');
    }

    /**
     * Determine whether the user can impersonate other users.
     */
    public function impersonate(AuthUser $authUser): bool
    {
        return $authUser->can('impersonate_user');
    }

    /**
     * Determine whether the user can send emails to other users.
     */
    public function sendEmail(AuthUser $authUser): bool
    {
        return $authUser->can('send_email_user');
    }
}
