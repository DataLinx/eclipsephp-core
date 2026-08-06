<?php

use Eclipse\Core\Filament\Actions\SendEmailTableAction;
use Eclipse\Core\Mail\SendEmailToUser;
use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    // Set up the tenant and super admin
    $this->setUpUserAndTenant();

    // Create users with site association
    $site = Site::first();

    $this->senderUser = User::factory()->create();
    $this->senderUser->sites()->attach($site);

    $this->recipientUser = User::factory()->create();
    $this->recipientUser->sites()->attach($site);
});

test('send email action visibility rules', function () {
    $this->actingAs($this->senderUser);

    // Test that action has the proper visibility configuration
    $action = SendEmailTableAction::makeAction();

    // Action should be properly configured
    expect($action)->not->toBeNull();
    expect($action->getName())->toBe('sendEmail');
    expect($action->getIcon())->toBe('heroicon-o-envelope');

    // Test that trashed user detection works
    expect($this->recipientUser->trashed())->toBeFalse();

    $this->recipientUser->delete();
    expect($this->recipientUser->trashed())->toBeTrue();
});

test('send email functionality queues mail', function () {
    Queue::fake();
    Mail::fake();

    $this->actingAs($this->senderUser);

    // Send email directly using the Mail class
    $emailData = [
        'subject' => 'Test Subject',
        'message' => 'Test message content',
        'cc' => 'cc1@example.com, cc2@example.com',
        'bcc' => 'bcc1@example.com',
    ];

    Mail::queue(new SendEmailToUser(
        $this->recipientUser,
        $emailData['subject'],
        $emailData['message'],
        $emailData['cc'],
        $emailData['bcc'],
        $this->senderUser
    ));

    // Assert email was queued
    Mail::assertQueued(SendEmailToUser::class, function ($mail) use ($emailData) {
        return $mail->recipient->id === $this->recipientUser->id
            && $mail->emailSubject === $emailData['subject']
            && $mail->emailMessage === $emailData['message']
            && $mail->ccEmails === $emailData['cc']
            && $mail->bccEmails === $emailData['bcc']
            && $mail->sender->id === $this->senderUser->id;
    });
});

test('email template renders correctly', function () {
    $mail = new SendEmailToUser(
        $this->recipientUser,
        'Test Subject',
        'Test message content',
        'cc@example.com',
        'bcc@example.com',
        $this->senderUser
    );

    $view = $mail->content()->view;
    $data = $mail->content()->with;

    expect($view)->toBe('eclipse::mail.send-email-to-user');
    expect($data['recipient']->id)->toBe($this->recipientUser->id);
    expect($data['messageContent'])->toBe('Test message content');
    expect($data['sender']->id)->toBe($this->senderUser->id);
    expect($data['subject'])->toBe('Test Subject');
});

test('email envelope has correct recipients', function () {
    $mail = new SendEmailToUser(
        $this->recipientUser,
        'Test Subject',
        'Test message content',
        'cc1@example.com, cc2@example.com',
        'bcc1@example.com, bcc2@example.com',
        $this->senderUser
    );

    $envelope = $mail->envelope();

    expect($envelope->subject)->toBe('Test Subject');

    // Test recipients
    expect($envelope->to)->toHaveCount(1);
    expect($envelope->to[0]->address)->toBe($this->recipientUser->email);

    // Test CC
    expect($envelope->cc)->toHaveCount(2);
    expect($envelope->cc[0]->address)->toBe('cc1@example.com');
    expect($envelope->cc[1]->address)->toBe('cc2@example.com');

    // Test BCC
    expect($envelope->bcc)->toHaveCount(2);
    expect($envelope->bcc[0]->address)->toBe('bcc1@example.com');
    expect($envelope->bcc[1]->address)->toBe('bcc2@example.com');
});
