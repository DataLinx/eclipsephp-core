<?php

namespace Eclipse\Core\Models;

use Eclipse\Core\Support\CurrentSite;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification as BaseDatabaseNotification;

class DatabaseNotification extends BaseDatabaseNotification
{
    /**
     * Allow storing tenant/site identifier alongside the notification payload.
     */
    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'site_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('site', function (Builder $builder): void {
            $siteId = static::resolveCurrentSiteId();

            if ($siteId !== null) {
                $builder->where($builder->getModel()->getTable().'.site_id', $siteId);
            }

        });
    }

    protected static function resolveCurrentSiteId(): ?int
    {
        // 1) Global context if available
        if ($id = app(CurrentSite::class)->get()) {
            return $id;
        }

        // 2) Filament tenant if available
        if ($tenantId = Filament::getTenant()?->getKey()) {
            return $tenantId;
        }

        // 3) Hostname mapping to a site
        if ($host = request()?->getHost()) {
            return Site::query()->where('domain', $host)->value('id');
        }

        return null;
    }
}
