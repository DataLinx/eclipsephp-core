<?php

namespace Eclipse\Core\Models;

use Eclipse\Core\Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Exceptions\UnauthorizedException;

/**
 * @property int $id
 * @property-read string|null $name
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $email
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class User extends Authenticatable implements FilamentUser, HasAvatar, HasMedia, HasTenants
{
    use HasFactory, HasRoles, InteractsWithMedia, Notifiable, SoftDeletes;

    protected $table = 'users';
    protected $dates = ['deleted_at'];

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
        'last_login_at',
        'login_count',
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
            'last_login_at' => 'datetime',
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

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    protected static function booted()
    {
        static::saving(function (self $user) {
            $user->name = trim("$user->first_name $user->last_name");
        });

        static::retrieved(function (self $user) {
            if ($user->trashed() && auth()->check() && request()->routeIs('login')) {
                throw new \Exception('This account has been deactivated.');
            }
        });        
    }

    /**
     * Update the user's last login timestamp and increment login count.
     *
     * @return void
     */
    public function updateLoginTracking()
    {
        $this->last_login_at = now();
        $this->increment('login_count');
        $this->save();
    }

    /**
     * Delete the user account, preventing self-deletion.
     *
     * @throws \Exception If the user attempts to delete their own account.
     * @return bool|null
     */
    public function delete(): ?bool
    {
        $authUser = auth()->user();

        if (!$authUser || !$authUser->hasAnyRole(['super-admin']) && !$authUser->hasPermissionTo('delete users')) {
            throw new UnauthorizedException(403, 'You do not have permission to delete users.');
        }

        if ($this->id === $authUser->id) {
            throw new \Exception('You cannot delete your own account.');
        }

        return parent::delete();
    }

    public function restore(): bool
    {
        $authUser = auth()->user();

        if (!$authUser || !$authUser->hasAnyRole(['super-admin']) && !$authUser->hasPermissionTo('restore users')) {
            throw new UnauthorizedException(403, 'You do not have permission to restore users.');
        }

        return parent::restore();
    }
}
