<?php

namespace Eclipse\Core\Mail;

use Eclipse\Core\Models\User;
use Eclipse\Core\Services\Registry;
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
use Throwable;

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
        public ?User $sender = null,
        public ?int $siteId = null
    ) {
        $this->emailMessage = $this->purifyHtml($this->emailMessage);
        $this->siteId = $this->siteId ?? Registry::getSite()?->id;
    }

    /**
     * Purify the HTML content.
     */
    protected function purifyHtml(string $html): string
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Trusted', true);
        $config->set('Core.Encoding', 'UTF-8');

        return (new HTMLPurifier($config))->purify($html);
    }

    /**
     * Get the envelope for the email.
     */
    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: $this->emailSubject,
            to: [$this->recipient->email]
        );

        if ($this->sender?->email) {
            $envelope = $envelope->replyTo($this->sender->email, $this->sender->name);
        }

        if ($ccEmails = $this->parseEmailList($this->ccEmails)) {
            $envelope = $envelope->cc($ccEmails);
        }

        if ($bccEmails = $this->parseEmailList($this->bccEmails)) {
            $envelope = $envelope->bcc($bccEmails);
        }

        return $envelope;
    }

    /**
     * Parse the email list.
     */
    protected function parseEmailList(?string $emails): array
    {
        if (! $emails) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $emails)));
    }

    /**
     * Get the headers for the email.
     */
    public function headers(): Headers
    {
        return new Headers(text: [
            'X-Eclipse-Email-Type' => 'SendEmailToUser',
            'X-Eclipse-Sender-ID' => (string) ($this->sender?->id ?? ''),
            'X-Eclipse-Recipient-ID' => (string) $this->recipient->id,
            'X-Eclipse-Recipient-Email' => $this->recipient->email,
            'X-Eclipse-Site-ID' => (string) ($this->siteId ?? ''),
        ]);
    }

    /**
     * Get the content for the email.
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
     * Get the attachments for the email.
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Handle the failed event.
     */
    public function failed(Throwable $exception): void
    {
        if (! $this->sender) {
            return;
        }

        Notification::make()
            ->title(__('eclipse::email.error'))
            ->body(__('eclipse::email.send_error_message', ['error' => $exception->getMessage()]))
            ->danger()
            ->sendToDatabase($this->sender)
            ->broadcast([$this->sender]);
    }
}
