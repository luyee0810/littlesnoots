<?php

namespace App\Notifications;

use App\Models\AdoptionApplication;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** To the applicant, once the lister has decided either way. */
class AdoptionApplicationDecided extends Notification
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
        $approved = $this->application->status === 'approved';

        $mail = (new MailMessage)
            ->subject($approved
                ? "Good news about {$pet->name}"
                : "About your application for {$pet->name}")
            ->greeting("Hello {$this->application->applicant_name},");

        $mail = $approved
            ? $mail->line("Your application to adopt **{$pet->name}** has been approved. Whoever is rehoming them will be in touch to arrange the next steps.")
            : $mail->line("Thank you for your interest in **{$pet->name}**. On this occasion they've gone to a different home.");

        if ($this->application->staff_notes) {
            $mail->line('**A note for you:**')->line($this->application->staff_notes);
        }

        return $approved
            ? $mail->action("View {$pet->name}", route('pets.show', $pet))
                ->line('Thank you for adopting rather than shopping.')
            : $mail->action('See other pets looking for homes', route('pets.index'))
                ->line('There are plenty of others hoping for a home — we hope you find your match.');
    }
}
