<?php

namespace Eclipse\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Generate Reverb WebSocket server credentials for Laravel Broadcasting.
 *
 * This command generates the required credentials for Laravel Reverb WebSocket server:
 * - REVERB_APP_ID: Random 6-digit number (100,000 - 999,999)
 * - REVERB_APP_KEY: Random 20-character lowercase string
 * - REVERB_APP_SECRET: Random 20-character lowercase string
 *
 * The credentials are automatically added to the .env file. If credentials already exist,
 * use the --force flag to overwrite them.
 *
 * This replaces the need to run `php artisan install:broadcasting` for new projects,
 * ensuring Reverb is configured out-of-the-box.
 *
 * @example php artisan eclipse:generate-reverb-credentials
 * @example php artisan eclipse:generate-reverb-credentials --force
 */
class GenerateReverbCredentialsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eclipse:generate-reverb-credentials 
                            {--force : Force overwrite existing credentials}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate random Reverb credentials (APP_ID, APP_KEY, APP_SECRET) for WebSocket broadcasting';

    /**
     * Execute the console command.
     *
     * Generates new Reverb credentials and updates the .env file.
     * Uses the same generation logic as Laravel Reverb's install command.
     *
     * @return int Command exit code (SUCCESS or FAILURE)
     */
    public function handle(): int
    {
        if (File::missing($env = app()->environmentFile())) {
            $this->components->error('Environment file not found.');

            return self::FAILURE;
        }

        $contents = File::get($env);

        // Check if credentials already exist
        $hasCredentials = Str::contains($contents, 'REVERB_APP_ID=') &&
            ! Str::contains($contents, 'REVERB_APP_ID=null');

        if ($hasCredentials && ! $this->option('force')) {
            $this->components->warn('Reverb credentials already exist in .env file.');
            $this->components->info('Use --force to overwrite existing credentials.');

            return self::SUCCESS;
        }

        // Generate credentials using the same logic as Laravel Reverb
        $appId = random_int(100_000, 999_999);
        $appKey = Str::lower(Str::random(20));
        $appSecret = Str::lower(Str::random(20));

        $this->updateEnvironmentFile($env, $contents, $appId, $appKey, $appSecret);

        $this->components->info('Reverb credentials generated successfully:');
        $this->components->twoColumnDetail('REVERB_APP_ID', $appId);
        $this->components->twoColumnDetail('REVERB_APP_KEY', $appKey);
        $this->components->twoColumnDetail('REVERB_APP_SECRET', '***'.substr($appSecret, -4));

        return self::SUCCESS;
    }

    /**
     * Update the environment file with new Reverb credentials.
     *
     * This method handles both updating existing credentials and adding new ones.
     * It uses regex replacement for existing values and appends new lines for missing keys.
     *
     * @param  string  $envPath  Path to the .env file
     * @param  string  $contents  Current contents of the .env file
     * @param  int  $appId  Generated Reverb application ID
     * @param  string  $appKey  Generated Reverb application key
     * @param  string  $appSecret  Generated Reverb application secret
     */
    protected function updateEnvironmentFile(string $envPath, string $contents, int $appId, string $appKey, string $appSecret): void
    {
        $replacements = [
            'REVERB_APP_ID' => "REVERB_APP_ID={$appId}",
            'REVERB_APP_KEY' => "REVERB_APP_KEY={$appKey}",
            'REVERB_APP_SECRET' => "REVERB_APP_SECRET={$appSecret}",
        ];

        $newContents = $contents;

        foreach ($replacements as $key => $value) {
            if (Str::contains($newContents, "{$key}=")) {
                // Replace existing value
                $newContents = preg_replace(
                    "/^{$key}=.*$/m",
                    $value,
                    $newContents
                );
            } else {
                // Add new line if it doesn't exist
                $newContents = rtrim($newContents).PHP_EOL.$value.PHP_EOL;
            }
        }

        File::put($envPath, $newContents);
    }
}
