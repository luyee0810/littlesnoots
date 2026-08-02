<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /** Either side of the booking may view it; admins may view any. */
    public function view(User $user, Booking $booking): bool
    {
        return $this->isOwner($user, $booking)
            || $this->isProvider($user, $booking)
            || $user->isAdmin();
    }

    /** Only the provider accepts or declines, and only while it's pending. */
    public function respond(User $user, Booking $booking): bool
    {
        return $this->isProvider($user, $booking) && $booking->status === 'pending';
    }

    /** Marking the job done is the provider's call, once it's confirmed. */
    public function complete(User $user, Booking $booking): bool
    {
        return $this->isProvider($user, $booking)
            && in_array($booking->status, ['accepted', 'in_progress'], true);
    }

    /** Either side may back out while the booking is still live. */
    public function cancel(User $user, Booking $booking): bool
    {
        if (! in_array($booking->status, ['pending', 'accepted', 'in_progress'], true)) {
            return false;
        }

        return $this->isOwner($user, $booking) || $this->isProvider($user, $booking);
    }

    private function isOwner(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id;
    }

    private function isProvider(User $user, Booking $booking): bool
    {
        return $booking->providerProfile !== null
            && $booking->providerProfile->user_id === $user->id;
    }
}
