<?php

namespace Eclipse\Core\Console\Commands;

use Illuminate\Console\Command;

class OptimizeCommand extends Command
{
    protected $signature = 'eclipse:optimize';

    protected $description = 'Run the optimization procedure';

    public function handle(): void
    {
        $this->line('Running optimization procedure...');

        // Laravel config and route caching
        // ------------------
        $this->call('optimize');

        // Filament optimization
        // ------------------
        $this->call('filament:optimize');

        // ------------------

        $this->newLine();

        $this->info('Optimization procedure complete!');
    }
}
