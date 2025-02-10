<?php

namespace Eclipse\Core\Models;

use Eclipse\Core\Foundation\Model\HasCompositeAttributes;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $email
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read string $full_name User's full name
 */
class User extends Authenticatable implements FilamentUser, HasAvatar, HasMedia, HasName, HasTenants
{
    use HasCompositeAttributes, HasFactory, HasRoles, InteractsWithMedia, Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the sites.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sites()
    {
        return $this->belongsToMany(Site::class, 'site_has_user');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->getMedia('avatars')->first()?->getUrl();
    }

    public function getFilamentName(): string
    {
        return "$this->first_name $this->last_name";
    }

    protected static function defineCompositeAttributes(): array
    {
        switch (DB::getDriverName()) {
            case 'sqlite':
                return [
                    'full_name' => "users.first_name || ' ' || users.last_name",
                ];
            default:
                return [
                    'full_name' => "TRIM(CONCAT(IFNULL(users.first_name, ''), ' ', IFNULL(users.last_name, '')))",
                ];
        }
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->sites()->whereKey($tenant)->exists();
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->sites()->where('is_active', true)->get();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
