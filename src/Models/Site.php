<?php

namespace Eclipse\Core\Models;

use Eclipse\Core\Database\Factories\SiteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['domain', 'name', 'is_active', 'is_secure'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'is_active' => 'boolean',
            'is_secure' => 'boolean',
        ];
    }

    protected static function newFactory(): SiteFactory
    {
        return SiteFactory::new();
    }

    /** @return HasMany<\Eclipse\Core\Models\User\Role, self> */
    public function roles(): HasMany
    {
        return $this->hasMany(\Eclipse\Core\Models\User\Role::class);
    }
}
