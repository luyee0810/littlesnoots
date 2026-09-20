<?php

namespace App\Support;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as Notifier;

/**
 * Sends a notification without letting mail failures break the action.
 *
 * Mail is sent synchronously: shared hosting can't run a queue worker, so a
 * queued notification would sit in the jobs table forever. The trade-off is
 * that the SMTP round-trip happens during the request — so a mail server
 * outage must never turn a successful approval into a 500. Failures are
 * logged and swallowed; the thing being notified about has already happened.
 */
class Notify
{
    public static function send(mixed $notifiable, Notification $notification): void
    {
        if ($notifiable === null) {
            return;
        }

        try {
            Notifier::send(collect([$notifiable])->filter(), $notification);
        } catch (\Throwable $e) {
            Log::warning('Notification failed to send', [
                'notification' => $notification::class,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** For recipients we only hold an email address for (booking snapshots). */
    public static function toEmail(?string $email, Notification $notification): void
    {
        if (! $email) {
            return;
        }

        try {
            Notifier::route('mail', $email)->notify($notification);
        } catch (\Throwable $e) {
            Log::warning('Notification failed to send', [
                'notification' => $notification::class,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
