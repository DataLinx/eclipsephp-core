<?php

namespace Eclipse\Core\Services;

use Filament\Contracts\Plugin;
use InvalidArgumentException;

/**
 * Registry for managing Filament plugins in the application.
 *
 * This class serves as a central registry for Filament plugins, allowing them to be:
 * - Registered in the AppServiceProvider during application bootstrap
 * - Retrieved and configured in the AdminPanelProvider for the admin panel setup
 * - Managed as a collection that can be accessed throughout the application
 *
 * The registry supports adding both single plugins and arrays of plugins,
 * ensuring type safety by validating that all registered items implement
 * the Filament\Contracts\Plugin interface.
 */
class PluginRegistry
{
    /**
     * @var Plugin[]
     */
    protected array $plugins = [];

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
