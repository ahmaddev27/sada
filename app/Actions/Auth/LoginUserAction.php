<?php

namespace App\Actions\Auth;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

// AUTH-05: remember me (30-day token via Laravel's built-in mechanism)
// AUTH-06: rate limiting delegated to LoginRequest
class LoginUserAction
{
    public function execute(LoginRequest $request): void
    {
        $request->ensureIsNotRateLimited();

        $credentials = [
            'email'    => $request->string('email')->lower()->toString(),
            'password' => $request->string('password')->toString(),
        ];

        if (! Auth::attempt($credentials, remember: $request->boolean('remember'))) {
            $request->incrementRateLimiter();

            throw ValidationException::withMessages([
                'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
            ]);
        }

        $request->clearRateLimiter();
        $request->session()->regenerate();
    }
}
