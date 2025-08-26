<?php

namespace Eclipse\Core\Notifications\Channels;

use Eclipse\Core\Notifications\Concerns\ResolvesSiteId;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Notification;

class SiteDatabaseChannel extends DatabaseChannel
{
    use ResolvesSiteId;

    /**
     * Build the payload stored in the notifications table and
     * append the current site id so rows are tenant-aware.
     */
    protected function buildPayload($notifiable, Notification $notification): array
    {
        $payload = parent::buildPayload($notifiable, $notification);
        $fromNotification = $this->resolveSiteIdFromNotification($notification);
        $resolved = $fromNotification ?? $this->resolveSiteId();
        $payload['site_id'] = $resolved;

        return $payload;
    }

    /**
     * Prefer an explicit site id coming from the notification instance
     * (useful when dispatching from queues) before resolving ambient context.
     */
    protected function resolveSiteIdFromNotification(Notification $notification): ?int
    {
        if (method_exists($notification, 'getSiteId')) {
            return $notification->getSiteId();
        }

        return null;
    }
}
