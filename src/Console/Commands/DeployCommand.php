<?php

namespace Eclipse\Core\Console\Commands;

use Illuminate\Console\Command;

class DeployCommand extends Command
{
    protected $signature = 'eclipse:deploy';

    protected $description = 'Run the deployment procedure';

    public function handle(): void
    {
        $this->line('Running deployment procedure...');

        // Laravel config and route caching
        // ------------------
        $this->call('optimize');

        // Filament optimization
        // ------------------
        $this->call('filament:optimize');

        // Terminate Horizon workers so that any code changes are received
        // ------------------
        $this->call('horizon:terminate');

        // ------------------

        $this->newLine();

        $this->info('Deployment procedure complete!');
    }
}
