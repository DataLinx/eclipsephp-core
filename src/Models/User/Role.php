<?php

namespace Eclipse\Core\Models\User;

use Eclipse\Core\Models\Site;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
