<?php

namespace Eclipse\Core\Services;

use Eclipse\Core\Models\Site;
use Filament\Contracts\Plugin;
use Illuminate\Support\Facades\Context;
use InvalidArgumentException;

/**
 * Class for registering things that can be used in other things :)
 */
class Registry
{
    private static Site $current_site;

    /**
     * @var Plugin[]
     */
    protected array $plugins = [];

    /**
     * Set current site
     */
    public static function setSite(int|Site $site): void
    {
        if ($site instanceof Site) {
            self::$current_site = $site;
        } else {
            self::$current_site = Site::find($site);
        }

        Context::add('site', self::getSite()->id);

        setPermissionsTeamId($site->id);
    }

    /**
     * Get current site
     */
    public static function getSite(): ?Site
    {
        return self::$current_site ?? null;
    }

    /**
     * Add plugin to registry
     *
     * @param  Plugin|Plugin[]  $plugin  Plugin instance or array of plugins
     */
    public function addPlugin(Plugin|array $plugin): void
    {
        if (is_array($plugin)) {
            foreach ($plugin as $p) {
                if (! $p instanceof Plugin) {
                    throw new InvalidArgumentException('Plugin must be an instance of Filament\\Contracts\\Plugin!');
                }
                $this->plugins[] = $p;
            }
        } else {
            $this->plugins[] = $plugin;
        }
    }

    /**
     * Get all registered plugins
     *
     * @return Plugin[]
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }
}
