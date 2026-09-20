<?php

namespace App\Notifications;

use App\Models\AdoptionApplication;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * To whoever listed the pet. Without this an application sits unseen until the
 * lister happens to log in — the single most time-sensitive event on the site.
 */
class AdoptionApplicationReceived extends Notification
{
    public function __construct(private AdoptionApplication $application) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pet = $this->application->pet;

        $mail = (new MailMessage)
            ->subject("Someone wants to adopt {$pet->name}")
            ->greeting('You have a new enquiry')
            ->line("**{$this->application->applicant_name}** has applied to adopt **{$pet->name}**.")
            ->line("Email: {$this->application->applicant_email}");

        if ($this->application->applicant_phone) {
            $mail->line("Phone: {$this->application->applicant_phone}");
        }

        if ($this->application->message) {
            $mail->line('**Their message:**')->line($this->application->message);
        }

        return $mail
            ->action("View {$pet->name}'s listing", route('listings.edit', $pet))
            ->line('Please get back to them directly — applicants are waiting to hear from you.');
    }
}
