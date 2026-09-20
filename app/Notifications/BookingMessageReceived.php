<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A new chat message.
 *
 * Deliberately says who wrote and where to reply, but not what they said —
 * the thread is private to the booking, and email is the wrong place to
 * mirror it.
 */
class BookingMessageReceived extends Notification
{
    public function __construct(private Booking $booking, private User $sender) {}

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
            ->subject("New message from {$this->sender->name}")
            ->greeting('You have a new message')
            ->line("**{$this->sender->name}** sent you a message about booking {$this->booking->reference} ({$this->booking->pet_name}).")
            ->action('Read and reply', route('bookings.show', $this->booking).'#messages')
            ->line('Replies stay on Little Snoots so there’s a record if anything goes wrong.');
    }
}
