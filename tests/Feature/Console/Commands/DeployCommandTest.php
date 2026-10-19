<?php

test('it runs the deploy command', function () {
    $this->artisan('eclipse:deploy')
        ->expectsOutput('Running deployment procedure...')
        ->expectsOutput('Deployment procedure complete!')
        ->assertSuccessful();
});
