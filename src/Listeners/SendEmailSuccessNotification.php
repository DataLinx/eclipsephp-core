<?php

namespace Eclipse\Core\Listeners;

use Eclipse\Core\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Mail\Events\MessageSent;

class SendEmailSuccessNotification
{
    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        $message = $event->message;

        $headers = $message->getHeaders();

        if (! $headers->has('X-Eclipse-Email-Type') ||
            $headers->get('X-Eclipse-Email-Type')->getBodyAsString() !== 'SendEmailToUser') {
            return;
        }

        if (! $headers->has('X-Eclipse-Sender-ID')) {
            return;
        }

        $senderId = $headers->get('X-Eclipse-Sender-ID')->getBodyAsString();
        if (empty($senderId)) {
            return;
        }

        if (! $headers->has('X-Eclipse-Recipient-Email')) {
            return;
        }

        $recipientEmail = $headers->get('X-Eclipse-Recipient-Email')->getBodyAsString();

        $sender = User::find($senderId);
        if (! $sender) {
            return;
        }

        Notification::make()
            ->title(__('eclipse::email.email_sent'))
            ->body(__('eclipse::email.email_sent_to', ['email' => $recipientEmail]))
            ->success()
            ->sendToDatabase($sender)
            ->broadcast([$sender]);
    }
}
