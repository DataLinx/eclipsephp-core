<?php

namespace Eclipse\Core\Filament\Resources\UserResource\Pages;

use Eclipse\Core\Filament\Resources\UserResource;
use Eclipse\Core\Filament\Resources\UserResource\Pages\Concerns\HandlesRoles;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use HandlesRoles;

    protected static string $resource = UserResource::class;
}
