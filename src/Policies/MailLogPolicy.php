<?php

namespace Eclipse\Core\Policies;

use Eclipse\Core\Models\MailLog;
use Eclipse\Core\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MailLogPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_mail::log');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MailLog $mailLog): bool
    {
        return $user->can('view_mail::log');
    }
}
