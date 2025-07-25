<?php

namespace Tests\Unit\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\SentMessage as LaravelSentMessage;
use Eclipse\Core\Listeners\LogEmailToDatabase;
use Eclipse\Core\Models\MailLog;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\IdentificationHeader;
use Mockery;

// Tests for the LogEmailToDatabase listener

test('it logs email to database when MessageSent event is handled', function () {
    // Create a Symfony Email instance with body and recipients
    $email = (new Email())
        ->subject('Hello World')
        ->text('Plain text body')
        ->html('<p>HTML body</p>')
        ->from('from@example.com')
        ->to('to@example.com')
        ->cc('cc@example.com')
        ->bcc('bcc@example.com');

    $headers = $email->getHeaders();

    // Add Message-ID header manually
    $headers->add(new IdentificationHeader('Message-ID', 'test-123@example.com'));

    // Add custom application headers
    $headers->addTextHeader('X-Eclipse-Site-ID', '123');
    $headers->addTextHeader('X-Eclipse-Sender-ID', '456');
    $headers->addTextHeader('X-Eclipse-Recipient-ID', '789');

    // Create a mock SentMessage that wraps the Email
    $sentMessage = Mockery::mock(LaravelSentMessage::class);
    $sentMessage->shouldReceive('getOriginalMessage')->andReturn($email);

    // Invoke the listener with the MessageSent event
    (new LogEmailToDatabase())->handle(new MessageSent($sentMessage, []));

    $log = MailLog::first();

    expect(MailLog::count())->toBe(1)
        ->and($log->message_id)->toBe('<test-123@example.com>')
        ->and($log->site_id)->toBe(123)
        ->and($log->sender_id)->toBe(456)
        ->and($log->recipient_id)->toBe(789)
        ->and($log->from)->toBe('from@example.com')
        ->and($log->to)->toBe('to@example.com')
        ->and($log->cc)->toBe('cc@example.com')
        ->and($log->bcc)->toBe('bcc@example.com')
        ->and($log->subject)->toBe('Hello World')
        ->and($log->body)->toBe('<p>HTML body</p>')
        ->and(is_array($log->headers))->toBeTrue()
        ->and($log->status)->toBe('sent');

    expect($log->sent_at)->not()->toBeNull();
});


test('it handles missing headers gracefully and still logs minimum fields', function () {
    // Create Email with only required From/To
    $email = (new Email())
        ->subject('No Headers')
        ->html('<p>Body only</p>')
        ->from('foo@example.com')
        ->to('bar@example.com');

    // No custom headers added

    $sentMessage = Mockery::mock(LaravelSentMessage::class);
    $sentMessage->shouldReceive('getOriginalMessage')->andReturn($email);

    (new LogEmailToDatabase())->handle(new MessageSent($sentMessage, []));

    $log = MailLog::first();

    expect(MailLog::count())->toBe(1)
        ->and($log->message_id)->toBeNull()
        ->and($log->site_id)->toBeNull()
        ->and($log->sender_id)->toBeNull()
        ->and($log->recipient_id)->toBeNull()
        ->and($log->from)->toBe('foo@example.com')
        ->and($log->to)->toBe('bar@example.com')
        ->and($log->cc)->toBeNull()
        ->and($log->bcc)->toBeNull()
        ->and($log->subject)->toBe('No Headers')
        ->and($log->body)->toBe('<p>Body only</p>')
        ->and(is_array($log->headers))->toBeTrue()
        ->and($log->status)->toBe('sent');

    expect($log->sent_at)->not()->toBeNull();
});
