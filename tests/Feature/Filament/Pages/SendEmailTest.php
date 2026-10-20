<?php

use Eclipse\Core\Filament\Pages\Tools\SendEmail;
use Eclipse\Core\Mail\SendRawEmail;
use Illuminate\Support\Facades\Mail;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->setUpUserAndTenant();
});

test('from field uses the global from address', function () {
    config(['mail.from.address' => 'noreply@example.com']);

    livewire(SendEmail::class)
        ->assertSuccessful()
        ->assertSchemaStateSet(['from' => 'noreply@example.com']);
});

test('email can be sent', function () {
    Mail::fake();

    livewire(SendEmail::class)
        ->fillForm([
            'to' => 'one@domain.com, two@domain.com',
            'subject' => 'Hello',
            'message' => '<p>Body</p>',
        ])
        ->call('send')
        ->assertHasNoFormErrors();

    Mail::assertSent(SendRawEmail::class, fn ($m) => $m->hasTo('one@domain.com') && $m->hasTo('two@domain.com'));
});

test('required fields are validated', function () {
    livewire(SendEmail::class)
        ->fillForm(['to' => 'invalid'])
        ->call('send')
        ->assertHasFormErrors(['to', 'subject', 'message']);
});
