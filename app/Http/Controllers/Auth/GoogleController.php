<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateWithGoogleAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;

// AUTH-02: Google OAuth via Socialite
class GoogleController extends Controller
{
    public function redirect(): SymfonyRedirect|RedirectResponse
    {
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver('google');

        return $driver->stateless()->redirect();
    }

    public function callback(AuthenticateWithGoogleAction $action): RedirectResponse
    {
        /** @var AbstractProvider $driver */
        $driver     = Socialite::driver('google');
        $googleUser = $driver->stateless()->user();
        $user       = $action->execute($googleUser);

        auth()->login($user, remember: true);

        $hasWorkspace = $user->activeWorkspaces()->exists();

        return redirect()->intended(route($hasWorkspace ? 'dashboard' : 'onboarding'));
    }
}
