<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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
        $user = $action->execute(
            name:     $request->string('name')->trim()->toString(),
            email:    $request->string('email')->lower()->toString(),
            password: $request->string('password')->toString(),
        );

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
