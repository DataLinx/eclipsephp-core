<?php

namespace Eclipse\Core\Console\Commands;

use Illuminate\Console\Command;

class PostComposerUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eclipse:post-composer-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the post composer update procedure';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->line('Running Eclipse Post Composer update procedure...');

        // Publish Laravel assets
        // ------------------
        $this->call('vendor:publish', ['--tag' => 'laravel-assets', '--force' => true]);

        // ------------------

        $this->line('Eclipse Post Composer update procedure completed!');
    }
}
