<?php

namespace Eclipse\Core\Models\User;

use Eclipse\Core\Database\Factories\RoleFactory;
use Eclipse\Core\Models\Site;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    protected static function newFactory()
    {
        return RoleFactory::new();
    }
}
