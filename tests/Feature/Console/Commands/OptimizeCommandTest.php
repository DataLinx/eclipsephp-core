<?php

use Illuminate\Support\Facades\File;

/**
 * Cache files are written to an isolated directory, so the generated caches don't leak
 * into the shared workbench bootstrap cache, which would break other (parallel) tests.
 */
beforeEach(function () {
    $this->cachePath = sys_get_temp_dir().'/eclipse-optimize-'.uniqid();

    File::ensureDirectoryExists($this->cachePath);

    $this->cacheEnvVariables = [
        'APP_CONFIG_CACHE' => 'config.php',
        'APP_EVENTS_CACHE' => 'events.php',
        'APP_ROUTES_CACHE' => 'routes.php',
        'APP_SERVICES_CACHE' => 'services.php',
        'APP_PACKAGES_CACHE' => 'packages.php',
    ];

    foreach ($this->cacheEnvVariables as $name => $file) {
        $value = "{$this->cachePath}/{$file}";

        putenv("$name=$value");
        $_ENV[$name] = $_SERVER[$name] = $value;
    }

    config([
        'filament.cache_path' => "{$this->cachePath}/filament",
        'settings.discovered_settings_cache_path' => $this->cachePath,
    ]);
});

afterEach(function () {
    $this->artisan('optimize:clear');

    foreach (array_keys($this->cacheEnvVariables) as $name) {
        putenv($name);
        unset($_ENV[$name], $_SERVER[$name]);
    }

    File::deleteDirectory($this->cachePath);
});

test('it runs the optimize command and calls the expected sub-commands', function () {
    $this->artisan('eclipse:optimize')
        ->expectsOutput('Running optimization procedure...')
        ->expectsOutput('Optimization procedure complete!')
        ->assertSuccessful();

    expect("{$this->cachePath}/config.php")->toBeFile()
        ->and("{$this->cachePath}/events.php")->toBeFile()
        ->and("{$this->cachePath}/routes.php")->toBeFile();
});
