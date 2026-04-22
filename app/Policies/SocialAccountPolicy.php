<?php

namespace App\Policies;

use App\Models\SocialAccount;
use App\Models\User;

class SocialAccountPolicy
{
    public function update(User $user, SocialAccount $account): bool
    {
        return $account->workspace?->user_id === $user->id;
    }

    public function delete(User $user, SocialAccount $account): bool
    {
        return $account->workspace?->user_id === $user->id;
    }
}
