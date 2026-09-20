<?php

namespace App\Policies;

use App\Models\Pet;
use App\Models\User;

class PetPolicy
{
    /**
     * Published listings are public. An unpublished one — a draft, or something
     * waiting on moderation — is visible only to its lister and to staff.
     */
    public function view(?User $user, Pet $pet): bool
    {
        if ($pet->isPublished()) {
            return true;
        }

        return $user !== null && ($pet->isOwnedBy($user) || $user->isStaff());
    }

    /** Listing is a capability, not a role: any signed-in user may rehome a pet. */
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Pet $pet): bool
    {
        return $pet->isOwnedBy($user) || $user->isStaff();
    }

    public function delete(User $user, Pet $pet): bool
    {
        return $this->update($user, $pet);
    }

    /** Only staff moderate — a lister can't approve their own listing. */
    public function moderate(User $user): bool
    {
        return $user->isStaff();
    }

    /** Applications go to whoever listed the pet; staff can see them all. */
    public function viewApplications(User $user, Pet $pet): bool
    {
        return $pet->isOwnedBy($user) || $user->isStaff();
    }
}
