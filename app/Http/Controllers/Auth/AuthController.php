<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    // AUTH-01
    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    // AUTH-01, AUTH-03
    public function register(RegisterRequest $request, RegisterUserAction $action): RedirectResponse
    {
        if (! app(SiteSettingsService::class)->get('registration_open', true)) {
            return back()->withErrors(['email' => 'التسجيل مغلق مؤقتاً. يرجى التواصل مع الدعم.']);
        }

        $user = $action->execute(
            name:     $request->string('name')->trim()->toString(),
            email:    $request->string('email')->lower()->toString(),
            password: $request->string('password')->toString(),
        );

        Auth::login($user);

        return redirect()->route('verification.notice');
    }

    // AUTH-05
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    // AUTH-05, AUTH-06
    public function login(LoginRequest $request, LoginUserAction $action): RedirectResponse
    {
        $action->execute($request);

        $hasWorkspace = $request->user()->activeWorkspaces()->exists();

        return redirect()->intended(route($hasWorkspace ? 'dashboard' : 'onboarding'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // AUTH-04
    public function showForgotPassword(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    // AUTH-04
    public function forgotPassword(ForgotPasswordRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink(
            ['email' => $request->string('email')->lower()->toString()],
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __('تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.'));
        }

        return back()->withErrors(['email' => __('لم نتمكن من إيجاد حساب بهذا البريد الإلكتروني.')]);
    }

    // AUTH-04
    public function showResetPassword(string $token): Response
    {
        return Inertia::render('Auth/ResetPassword', ['token' => $token]);
    }

    // AUTH-04
    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill(['password' => bcrypt($password)])
                     ->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __('تم إعادة تعيين كلمة المرور بنجاح.'));
        }

        return back()->withErrors(['email' => __('رمز إعادة التعيين غير صالح أو منتهي الصلاحية.')]);
    }
}
