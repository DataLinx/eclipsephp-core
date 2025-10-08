<?php

declare(strict_types=1);

namespace Eclipse\Core\Policies;

use Eclipse\Core\Models\MailLog;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MailLogPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_mail_log');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(AuthUser $authUser, MailLog $mailLog): bool
    {
        return $authUser->can('view_mail_log');
    }
}
