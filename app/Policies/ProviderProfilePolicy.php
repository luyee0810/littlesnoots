<?php

namespace App\Policies;

use App\Models\ProviderProfile;
use App\Models\User;

class ProviderProfilePolicy
{
    /** Live profiles are public; a draft is visible only to its owner and admins. */
    public function view(?User $user, ProviderProfile $profile): bool
    {
        if ($profile->isLive()) {
            return true;
        }

        return $user !== null && ($profile->user_id === $user->id || $user->isAdmin());
    }

    public function create(User $user): bool
    {
        return ! $user->hasProviderProfile();
    }

    public function update(User $user, ProviderProfile $profile): bool
    {
        return $profile->user_id === $user->id || $user->isAdmin();
    }

    public function delete(User $user, ProviderProfile $profile): bool
    {
        return $this->update($user, $profile);
    }
}
