<?php

namespace Eclipse\Core\Models\User;

use Eclipse\Core\Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected static function newFactory()
    {
        return RoleFactory::new();
    }
}
