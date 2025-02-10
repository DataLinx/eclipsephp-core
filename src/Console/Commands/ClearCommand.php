<?php

namespace Eclipse\Core\Console\Commands;

use Illuminate\Console\Command;

class ClearCommand extends Command
{
    protected $signature = 'eclipse:clear';

    protected $description = 'Clear local caches';

    public function handle(): void
    {
        $this->line('Clearing caches...');

        // Clear Laravel cache
        // ------------------
        $this->call('optimize:clear');

        // Clear Filament cache
        // ------------------
        $this->call('filament:optimize-clear');

        // ------------------

        $this->newLine();

        $this->info('Cache cleared!');
    }
}
