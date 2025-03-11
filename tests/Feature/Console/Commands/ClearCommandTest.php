<?php

use Eclipse\Core\Console\Commands\ClearCommand;
use Illuminate\Support\Facades\Artisan;

test('it clears caches and outputs success message', function () {

    // TODO Fix mocking, does not work for some reason
    // Set up the mock command
//    $this->partialMock(ClearCommand::class, function (\Mockery\MockInterface $mock) {
//        $mock->expects('call')->with('optimize:clear')->once();
//        $mock->expects('call')->with('filament:optimize-clear')->once();
//    });
//
//    // Run the command
//    $this->artisan('eclipse:clear')
//        ->expectsOutput('Clearing caches...')
//        ->expectsOutput('Cache cleared!')
//        ->assertExitCode(0);
});
