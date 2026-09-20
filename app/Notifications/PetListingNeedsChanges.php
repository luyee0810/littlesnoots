<?php

namespace App\Notifications;

use App\Models\Pet;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PetListingNeedsChanges extends Notification
{
    public function __construct(private Pet $pet, private string $notes) {}

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
            ->subject("{$this->pet->name}'s listing needs a few changes")
            ->greeting('Thanks for listing with us')
            ->line("We've had a look at {$this->pet->name}'s listing and it isn't quite ready to go live.")
            ->line('**What needs changing:**')
            ->line($this->notes)
            ->action('Edit the listing', route('listings.edit', $this->pet))
            ->line('Once you’ve made the changes, send it back for review and we’ll take another look.');
    }
}
