<?php

namespace Eclipse\Core\Mail;

use Eclipse\Core\Models\User;
use Filament\Notifications\Notification;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class SendEmailToUser extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $recipient,
        public string $emailSubject,
        public string $emailMessage,
        public ?string $ccEmails = null,
        public ?string $bccEmails = null,
        public ?User $sender = null
    ) {
        $this->emailMessage = $this->purifyHtml($this->emailMessage);
    }

    /**
     * Purify HTML content to prevent XSS attacks and clean up malicious code.
     */
    protected function purifyHtml(string $html): string
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Trusted', true);
        $config->set('Core.Encoding', 'UTF-8');
        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: $this->emailSubject,
            to: [$this->recipient->email]
        );

        if ($this->sender && $this->sender->email) {
            $envelope = $envelope->replyTo($this->sender->email, $this->sender->name);
        }

        if ($this->ccEmails) {
            $ccEmailsArray = array_filter(array_map('trim', explode(',', $this->ccEmails)));
            if (! empty($ccEmailsArray)) {
                $envelope = $envelope->cc($ccEmailsArray);
            }
        }

        if ($this->bccEmails) {
            $bccEmailsArray = array_filter(array_map('trim', explode(',', $this->bccEmails)));
            if (! empty($bccEmailsArray)) {
                $envelope = $envelope->bcc($bccEmailsArray);
            }
        }

        return $envelope;
    }

    /**
     * Get the message headers.
     */
    public function headers(): Headers
    {
        return new Headers(
            text: [
                'X-Eclipse-Email-Type' => 'SendEmailToUser',
                'X-Eclipse-Sender-ID' => $this->sender?->id ?? '',
                'X-Eclipse-Recipient-Email' => $this->recipient->email,
            ]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'eclipse::mail.send-email-to-user',
            with: [
                'recipient' => $this->recipient,
                'messageContent' => $this->emailMessage,
                'sender' => $this->sender,
                'subject' => $this->emailSubject,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        if ($this->sender) {
            Notification::make()
                ->title(__('eclipse::email.error'))
                ->body(__('eclipse::email.send_error_message', [
                    'error' => $exception->getMessage(),
                ]))
                ->danger()
                ->sendToDatabase($this->sender)
                ->broadcast([$this->sender]);
        }
    }
}
