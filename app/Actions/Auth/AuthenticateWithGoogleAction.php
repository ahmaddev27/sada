<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Carbon;
use Laravel\Socialite\Contracts\User as SocialiteUser;

// AUTH-02: find-or-create user via Google OAuth
// Strategy: match by google_id first, then by email (covers existing email/password users)
class AuthenticateWithGoogleAction
{
    public function execute(SocialiteUser $googleUser): User
    {
        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->update([
                'google_id'          => $googleUser->getId(),
                'email_verified_at'  => $user->email_verified_at ?? Carbon::now(),
            ]);

            return $user;
        }

        return User::create([
            'name'               => $googleUser->getName(),
            'email'              => $googleUser->getEmail(),
            'google_id'          => $googleUser->getId(),
            'password'           => null,
            'email_verified_at'  => Carbon::now(), // Google guarantees verified email
            'token_balance'      => 0,
        ]);
    }
}
