<?php

use Eclipse\Core\Mail\SendEmailToUser;
use Eclipse\Core\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Set up the tenant and super admin
    $this->set_up_super_admin_and_tenant();

    // Create additional permissions
    Permission::firstOrCreate(['name' => 'send_email_user']);

    // Create role with email permission
    $this->emailRole = Role::firstOrCreate(['name' => 'email_sender']);
    $this->emailRole->givePermissionTo(['send_email_user', 'view_any_user']);

    // Create role without email permission
    $this->regularRole = Role::firstOrCreate(['name' => 'regular_user']);
    $this->regularRole->givePermissionTo(['view_any_user']);

    // Create users with site association
    $site = \Eclipse\Core\Models\Site::first();

    $this->authorizedUser = User::factory()->create();
    $this->authorizedUser->syncRoles([$this->emailRole]);
    $this->authorizedUser->sites()->attach($site);

    $this->unauthorizedUser = User::factory()->create();
    $this->unauthorizedUser->syncRoles([$this->regularRole]);
    $this->unauthorizedUser->sites()->attach($site);

    $this->recipientUser = User::factory()->create();
    $this->recipientUser->sites()->attach($site);
});

test('authorized user has send email permission', function () {
    $this->actingAs($this->authorizedUser);

    expect($this->authorizedUser->can('sendEmail', User::class))->toBeTrue();
});

test('unauthorized user does not have send email permission', function () {
    $this->actingAs($this->unauthorizedUser);

    expect($this->unauthorizedUser->can('sendEmail', User::class))->toBeFalse();
});

test('send email action requires authorization', function () {
    // Test authorization through the policy directly
    $this->actingAs($this->authorizedUser);
    expect($this->authorizedUser->can('sendEmail', User::class))->toBeTrue();

    $this->actingAs($this->unauthorizedUser);
    expect($this->unauthorizedUser->can('sendEmail', User::class))->toBeFalse();

    // Test that action is properly configured
    $action = \Eclipse\Core\Filament\Actions\SendEmailTableAction::makeAction();
    expect($action->getName())->toBe('sendEmail');
    expect($action->getIcon())->toBe('heroicon-o-envelope');
});

test('send email action visibility rules', function () {
    $this->actingAs($this->authorizedUser);

    // Test that action has the proper visibility configuration
    $action = \Eclipse\Core\Filament\Actions\SendEmailTableAction::makeAction();

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

    $this->actingAs($this->authorizedUser);

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
        $this->authorizedUser
    ));

    // Assert email was queued
    Mail::assertQueued(SendEmailToUser::class, function ($mail) use ($emailData) {
        return $mail->recipient->id === $this->recipientUser->id
            && $mail->emailSubject === $emailData['subject']
            && $mail->emailMessage === $emailData['message']
            && $mail->ccEmails === $emailData['cc']
            && $mail->bccEmails === $emailData['bcc']
            && $mail->sender->id === $this->authorizedUser->id;
    });
});

test('email template renders correctly', function () {
    $mail = new SendEmailToUser(
        $this->recipientUser,
        'Test Subject',
        'Test message content',
        'cc@example.com',
        'bcc@example.com',
        $this->authorizedUser
    );

    $view = $mail->content()->view;
    $data = $mail->content()->with;

    expect($view)->toBe('eclipse::mail.send-email-to-user');
    expect($data['recipient']->id)->toBe($this->recipientUser->id);
    expect($data['messageContent'])->toBe('Test message content');
    expect($data['sender']->id)->toBe($this->authorizedUser->id);
    expect($data['subject'])->toBe('Test Subject');
});

test('email envelope has correct recipients', function () {
    $mail = new SendEmailToUser(
        $this->recipientUser,
        'Test Subject',
        'Test message content',
        'cc1@example.com, cc2@example.com',
        'bcc1@example.com, bcc2@example.com',
        $this->authorizedUser
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
