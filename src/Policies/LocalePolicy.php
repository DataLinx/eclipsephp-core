<?php

declare(strict_types=1);

namespace Eclipse\Core\Policies;

use Eclipse\Core\Models\Locale;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LocalePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_locale');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_locale');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(AuthUser $authUser, Locale $locale): bool
    {
        return $authUser->can('update_locale');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(AuthUser $authUser, Locale $locale): bool
    {
        return $authUser->can('delete_locale');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any_locale');
    }
}
