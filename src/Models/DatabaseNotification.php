<?php

namespace Eclipse\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification as BaseDatabaseNotification;
use Illuminate\Support\Facades\Context;

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
            $siteId = Context::get('site');

            if ($siteId !== null) {
                $builder->where($builder->getModel()->getTable().'.site_id', $siteId);
            }
        });
    }
}
