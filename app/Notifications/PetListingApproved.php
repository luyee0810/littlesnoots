<?php

namespace App\Notifications;

use App\Models\Pet;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PetListingApproved extends Notification
{
    public function __construct(private Pet $pet) {}

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
            ->subject("{$this->pet->name}'s listing is live")
            ->greeting('Good news!')
            ->line("{$this->pet->name}'s listing has been approved and is now visible to adopters on Little Snoots.")
            ->action("View {$this->pet->name}'s page", route('pets.show', $this->pet))
            ->line('We’ll email you as soon as someone applies to adopt.');
    }
}
