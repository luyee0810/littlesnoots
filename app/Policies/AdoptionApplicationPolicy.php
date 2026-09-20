<?php

namespace App\Policies;

use App\Models\AdoptionApplication;
use App\Models\User;

/**
 * Applications belong to whoever listed the pet — a rescuer handles their own,
 * staff can see and step into any. The applicant sees their own too, but can
 * only withdraw it.
 */
class AdoptionApplicationPolicy
{
    public function view(User $user, AdoptionApplication $application): bool
    {
        return $this->handles($user, $application)
            || $application->user_id === $user->id;
    }

    /** Deciding the outcome: lister or staff only, never the applicant. */
    public function review(User $user, AdoptionApplication $application): bool
    {
        return $this->handles($user, $application);
    }

    /** Pulling out — only the applicant, and only while it's still open. */
    public function withdraw(User $user, AdoptionApplication $application): bool
    {
        return $application->user_id === $user->id && $application->isOpen();
    }

    private function handles(User $user, AdoptionApplication $application): bool
    {
        return $user->isStaff() || $application->pet?->isOwnedBy($user) === true;
    }
}
