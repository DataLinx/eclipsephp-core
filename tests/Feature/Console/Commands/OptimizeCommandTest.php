<?php

test('it runs the optimize command and calls the expected sub-commands', function () {
    $this->artisan('eclipse:optimize')
        ->expectsOutput('Running optimization procedure...')
        ->expectsOutput('Optimization procedure complete!')
        ->assertSuccessful();
});
