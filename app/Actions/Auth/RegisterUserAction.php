<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;

// AUTH-01: creates user, fires Registered event (triggers verification email)
class RegisterUserAction
{
    public function execute(string $name, string $email, string $password): User
    {
        $user = User::create([
            'name'          => $name,
            'email'         => $email,
            'password'      => $password,
            'token_balance' => 0,
        ]);

        event(new Registered($user));

        return $user;
    }
}
