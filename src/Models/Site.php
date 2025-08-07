<?php

namespace Eclipse\Core\Models;

use Eclipse\Core\Database\Factories\SiteFactory;
use Eclipse\Core\Models\User\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'site_has_user');
    }

    protected static function newFactory(): SiteFactory
    {
        return SiteFactory::new();
    }

    /** @return HasMany<Role, self> */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /** @return HasMany<\Eclipse\Cms\Models\Section, self> */
    public function sections(): HasMany
    {
        return $this->hasMany(\Eclipse\Cms\Models\Section::class);
    }

    /** @return HasMany<\Eclipse\Cms\Models\Page, self> */
    public function pages(): HasMany
    {
        return $this->hasMany(\Eclipse\Cms\Models\Page::class);
    }

    /** @return HasMany<\Eclipse\Catalogue\Models\Category, self> */
    public function categories(): HasMany
    {
        return $this->hasMany(\Eclipse\Catalogue\Models\Category::class);
    }

    /** @return HasMany<\Eclipse\Catalogue\Models\TaxClass, self> */
    public function taxClasses(): HasMany
    {
        return $this->hasMany(\Eclipse\Catalogue\Models\TaxClass::class);
    }
}
