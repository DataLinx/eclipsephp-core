<?php

namespace Eclipse\Core\Models;

use Eclipse\Core\Database\Factories\UserFactory;
use Eclipse\Core\Models\User\Address;
use Eclipse\Core\Settings\UserSettings;
use Eclipse\World\Models\Country;
use Exception;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\LaravelSettings\Settings;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

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
        'phone_number',
        'password',
        'country_id',
        'date_of_birth',
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
            'date_of_birth' => 'date',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the sites.
     *
     * @return BelongsToMany
     */
    public function sites()
    {
        return $this->belongsToMany(Site::class, 'site_has_user');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
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
                throw new Exception('This account has been deactivated.');
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
     * @throws Exception If the user attempts to delete their own account.
     */
    public function delete(): ?bool
    {
        if ($this->id === auth()->id()) {
            throw new Exception('You cannot delete your own account.');
        }

        return parent::delete();
    }

    /**
     * Determine if the user can impersonate other users.
     */
    public function canImpersonate(): bool
    {
        return $this->can('impersonate', User::class);
    }

    public function getSettings(string $settingsClass = UserSettings::class): Settings
    {
        return $settingsClass::forUser($this->id);
    }

    /**
     * Override notifications relation to use site-aware notification model.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * The channels the user receives notification broadcasts on.
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        $host = request()?->getHost();
        $tenantId = Site::query()->where('domain', $host)->value('id');

        return "Eclipse.Core.Models.User.{$this->id}.tenant.{$tenantId}";
    }
}
