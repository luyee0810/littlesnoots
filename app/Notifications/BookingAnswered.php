<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** To the owner, when a sitter accepts or declines. */
class BookingAnswered extends Notification
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
        $sitter = $booking->providerProfile?->user?->name ?? 'Your sitter';
        $accepted = $booking->status === 'accepted';

        $mail = (new MailMessage)
            ->subject($accepted
                ? "{$sitter} accepted your booking"
                : "{$sitter} can't take this booking")
            ->greeting($accepted ? 'Your booking is confirmed' : 'About your booking request')
            ->line($accepted
                ? "{$sitter} has accepted your request for {$booking->pet_name} ({$booking->reference})."
                : "{$sitter} isn't able to take your request for {$booking->pet_name} ({$booking->reference}).");

        if ($booking->provider_response) {
            $mail->line('**Their message:**')->line($booking->provider_response);
        }

        return $mail
            ->action('View your booking', route('bookings.show', $booking))
            ->line($accepted
                ? 'Payment is arranged directly with your sitter.'
                : 'Plenty of other sitters are available — have a look when you’re ready.');
    }
}
