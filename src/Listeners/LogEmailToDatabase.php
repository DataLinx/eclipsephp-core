<?php

namespace Eclipse\Core\Listeners;

use Eclipse\Core\Models\MailLog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class LogEmailToDatabase
{
    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        try {
            $message = $event->message;
            $headers = $message->getHeaders();

            $messageId = $this->extractHeaderValue($headers, 'Message-ID');
            $siteId = $this->extractHeaderValue($headers, 'X-Eclipse-Site-ID');
            $senderId = $this->extractHeaderValue($headers, 'X-Eclipse-Sender-ID');
            $recipientId = $this->extractHeaderValue($headers, 'X-Eclipse-Recipient-ID');

            MailLog::create([
                'site_id' => $siteId ? (int) $siteId : null,
                'message_id' => $messageId,
                'from' => $this->extractHeaderValue($headers, 'From'),
                'to' => $this->extractHeaderValue($headers, 'To'),
                'cc' => $this->extractHeaderValue($headers, 'Cc'),
                'bcc' => $this->extractHeaderValue($headers, 'Bcc'),
                'subject' => $message->getSubject() ?? '',
                'body' => $this->extractEmailBody($message),
                'headers' => $this->collectAllHeaders($headers),
                'sender_id' => $senderId ? (int) $senderId : null,
                'recipient_id' => $recipientId ? (int) $recipientId : null,
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Email logging failed: '.$e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Extract a header value from the headers.
     */
    private function extractHeaderValue(\Symfony\Component\Mime\Header\Headers $headers, string $headerName): ?string
    {
        if (! $headers->has($headerName)) {
            return null;
        }

        try {
            return $headers->get($headerName)->getBodyAsString();
        } catch (\Exception $e) {
            Log::warning("Failed to extract header {$headerName}: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Extract the email body from the message.
     */
    private function extractEmailBody(\Symfony\Component\Mime\Email $message): string
    {
        if (method_exists($message, 'getHtmlBody')) {
            return $message->getHtmlBody() ?? '';
        }

        if (method_exists($message, 'getTextBody')) {
            return $message->getTextBody() ?? '';
        }

        return '';
    }

    /**
     * Collect all headers from the message.
     */
    private function collectAllHeaders(\Symfony\Component\Mime\Header\Headers $headers): array
    {
        $allHeaders = [];

        foreach ($headers->all() as $name => $header) {
            $allHeaders[$name] = $header->getBodyAsString();
        }

        return $allHeaders;
    }
}
