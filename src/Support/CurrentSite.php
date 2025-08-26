<?php

namespace Eclipse\Core\Support;

class CurrentSite
{
    /**
     * Holds the current site identifier for the running context
     * (HTTP request, queued job, or console command).
     */
    protected ?int $id = null;

    /**
     * Set the current site identifier for the active execution context.
     */
    public function set(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get the current site identifier, or null if not set.
     */
    public function get(): ?int
    {
        return $this->id;
    }
}
