<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateWithGoogleAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;

// AUTH-02: Google OAuth via Socialite
class GoogleController extends Controller
{
    public function redirect(): SymfonyRedirect|RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(AuthenticateWithGoogleAction $action): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();
        $user       = $action->execute($googleUser);

        auth()->login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }
}
