<?php

test('it clears caches and outputs success message', function () {
    $this->artisan('eclipse:clear')
        ->expectsOutput('Clearing caches...')
        ->expectsOutput('Cache cleared!')
        ->assertSuccessful();
});
