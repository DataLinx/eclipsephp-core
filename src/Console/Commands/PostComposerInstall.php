<?php

namespace Eclipse\Core\Console\Commands;

use Illuminate\Console\Command;

class PostComposerInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eclipse:post-composer-install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the post composer install procedure';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->line('Running Eclipse post Composer install procedure...');

        // Install Telescope
        // ------------------
        if (config('telescope.enabled')) {
            $this->call('telescope:install');
        }

        // ------------------

        $this->newLine();

        $this->info('Eclipse post Composer install procedure complete!');
    }
}
