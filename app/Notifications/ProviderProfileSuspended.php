<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProviderProfileSuspended extends Notification
{
    public function __construct(private string $notes) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your sitter profile isn’t listed')
            ->greeting('About your sitter profile')
            ->line('Your profile isn’t currently listed on Little Snoots, so owners can’t book you.')
            ->line('**Why:**')
            ->line($this->notes)
            ->action('Update your profile', route('provider.profile.edit'))
            ->line('When you’ve sorted that out, send your profile back for review from your dashboard.');
    }
}
