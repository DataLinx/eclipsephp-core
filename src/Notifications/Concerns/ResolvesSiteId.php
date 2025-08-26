<?php

namespace Eclipse\Core\Notifications\Concerns;

use Eclipse\Core\Models\Site;
use Eclipse\Core\Support\CurrentSite;
use Filament\Facades\Filament;

trait ResolvesSiteId
{
    /**
     * Resolve the active site id for notification persistence.
     *
     * Priority:
     * 1) Global CurrentSite (works in HTTP, queue workers, and CLI)
     * 2) Filament tenant (when a tenant context is active)
     * 3) Hostname mapping to a site (fallback for edge cases)
     */
    protected function resolveSiteId(): ?int
    {
        // Prefer global CurrentSite context if present (works in HTTP, jobs, CLI)
        if ($id = app(CurrentSite::class)->get()) {
            return $id;
        }

        // Next prefer current Filament tenant if present
        $tenant = Filament::getTenant();
        if ($tenant) {
            return $tenant->getKey();
        }

        // Fallback to host-based detection used elsewhere in the core
        $host = request()?->getHost();
        if ($host) {
            $resolved = Site::query()->where('domain', $host)->value('id');

            return $resolved;
        }

        return null;
    }
}
