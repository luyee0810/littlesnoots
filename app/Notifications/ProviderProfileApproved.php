<?php

namespace App\Notifications;

use App\Models\ProviderProfile;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProviderProfileApproved extends Notification
{
    public function __construct(private ProviderProfile $profile) {}

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
            ->subject('You’re now listed as a sitter')
            ->greeting('Welcome aboard!')
            ->line('Your sitter profile has been approved, so pet owners can now find and book you on Little Snoots.')
            ->action('View your public profile', route('providers.show', $this->profile))
            ->line('Booking requests expire if they’re not answered, so keep an eye on your inbox.');
    }
}
