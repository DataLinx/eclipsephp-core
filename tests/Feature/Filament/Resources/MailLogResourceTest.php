<?php

use Eclipse\Core\Filament\Resources\MailLogResource;
use Eclipse\Core\Filament\Resources\MailLogResource\Pages\ListMailLogs;
use Eclipse\Core\Models\MailLog;
use Filament\Tables\Actions\ViewAction;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
});

test('authorized access can be allowed', function () {
    $this->get(MailLogResource::getUrl())
        ->assertOk();
});

test('mail logs table page can be rendered', function () {
    livewire(ListMailLogs::class)->assertSuccessful();
});

test('mail logs can be searched', function () {
    // Create 5 mail logs
    MailLog::factory()->count(5)->create();

    // Get first mail log
    $mailLog = MailLog::first();

    // Get second mail log
    $mailLog2 = MailLog::skip(1)->first();

    livewire(ListMailLogs::class)
        ->searchTable($mailLog->subject)
        ->assertSee($mailLog->subject);
});

test('mail logs can be filtered by status', function () {
    // Create mail logs with different statuses
    MailLog::factory()->create(['status' => 'sent']);
    MailLog::factory()->create(['status' => 'failed']);
    MailLog::factory()->create(['status' => 'sending']);

    $component = livewire(ListMailLogs::class);

    // Filter by sent status
    $component->filterTable('status', 'sent')
        ->assertSee('sent');
});

test('mail log can be viewed', function () {
    $mailLog = MailLog::factory()->create([
        'from' => 'test@example.com',
        'to' => 'recipient@example.com',
        'subject' => 'Test Email',
        'body' => '<p>Test email body</p>',
        'status' => 'sent',
    ]);

    livewire(ListMailLogs::class)
        ->assertSuccessful()
        ->assertTableActionExists(ViewAction::class)
        ->assertTableActionEnabled(ViewAction::class, $mailLog)
        ->callTableAction(ViewAction::class, $mailLog)
        ->assertSee($mailLog->subject)
        ->assertSee($mailLog->from)
        ->assertSee($mailLog->to);
});

test('mail logs table shows correct columns', function () {
    $mailLog = MailLog::factory()->create([
        'sent_at' => now(),
        'from' => 'sender@example.com',
        'to' => 'recipient@example.com',
        'subject' => 'Test Subject',
        'status' => 'sent',
    ]);

    livewire(ListMailLogs::class)
        ->assertSee($mailLog->from)
        ->assertSee($mailLog->to)
        ->assertSee($mailLog->subject)
        ->assertSee($mailLog->status);
});

test('mail logs are sorted by sent_at desc by default', function () {
    $oldMailLog = MailLog::factory()->create(['sent_at' => now()->subDays(1)]);
    $newMailLog = MailLog::factory()->create(['sent_at' => now()]);

    livewire(ListMailLogs::class)
        ->assertSeeInOrder([$newMailLog->subject, $oldMailLog->subject]);
});

test('mail logs table shows empty state when no records', function () {
    livewire(ListMailLogs::class)
        ->assertSuccessful()
        ->assertSee('No emails found');
});

test('mail log view action shows headers in modal', function () {
    $mailLog = MailLog::factory()->create([
        'headers' => [
            'From' => 'sender@example.com',
            'To' => 'recipient@example.com',
            'Subject' => 'Test Subject',
        ],
    ]);

    livewire(ListMailLogs::class)
        ->callTableAction(ViewAction::class, $mailLog)
        ->assertSee('From')
        ->assertSee('To')
        ->assertSee('Subject');
});

test('mail log view action modal has correct heading', function () {
    $mailLog = MailLog::factory()->create([
        'subject' => 'Test Email Subject',
    ]);

    livewire(ListMailLogs::class)
        ->callTableAction(ViewAction::class, $mailLog)
        ->assertSee(__('eclipse::email.view_email'))
        ->assertSee('Test Email Subject');
});
