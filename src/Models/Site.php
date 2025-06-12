<?php

namespace Eclipse\Core\Models;

use Eclipse\Core\Database\Factories\SiteFactory;
use Eclipse\Core\Models\User\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    protected static function booted(): void
    {
        static::created(function ($site): void {
            $allUserIDs = Role::whereNull('site_id')
                ->with('users')
                ->get()
                ->pluck('users.*.id')
                ->flatten()
                ->unique();

            if ($allUserIDs->isNotEmpty()) {
                $site->users()->syncWithoutDetaching($allUserIDs);
            }
        });
    }

    protected static function newFactory(): SiteFactory
    {
        return SiteFactory::new();
    }
}
