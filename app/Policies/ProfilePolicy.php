<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Profile;

class ProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->profile?->role === 'trainer';
    }

    public function view(User $user, Profile $profile): bool
    {
        return $user->profile?->role === 'trainer'
            && $profile->role === 'member'
            && $profile->trainer_id === $user->profile->id;
    }
}