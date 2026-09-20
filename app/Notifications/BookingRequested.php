<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** To the sitter. Requests expire, so this one is genuinely urgent. */
class BookingRequested extends Notification
{
    public function __construct(private Booking $booking) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;

        return (new MailMessage)
            ->subject("New booking request from {$booking->owner_name}")
            ->greeting('You have a new booking request')
            ->line("**{$booking->owner_name}** would like to book **{$booking->category?->name}** for {$booking->pet_name}.")
            ->line("Reference: {$booking->reference}")
            ->when($booking->starts_at, fn ($mail) => $mail->line(
                'Dates: '.$booking->starts_at->format('j M Y').
                ($booking->ends_at ? ' – '.$booking->ends_at->format('j M Y') : '')
            ))
            ->line('Total: RM '.number_format((float) $booking->total, 2))
            ->action('Respond to this request', route('provider.bookings.index'))
            ->line('Requests expire after '.Booking::RESPONSE_WINDOW_HOURS.' hours, so try to answer soon.');
    }
}
