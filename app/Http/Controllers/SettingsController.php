<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Settings/Index', [
            'user' => [
                'name'          => $user->name,
                'email'         => $user->email,
                'token_balance' => $user->token_balance,
                'created_at'    => $user->created_at,
                'avatar_url'    => $user->avatar_path
                    ? Storage::url($user->avatar_path)
                    : null,
            ],
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $request->user()->update($validated);

        return back()->with('flash', ['success' => 'تم تحديث الملف الشخصي بنجاح.']);
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar_path) {
            Storage::delete($user->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar_path' => $path]);

        return back()->with('flash', ['success' => 'تم تحديث الصورة الشخصية.']);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = $request->user();

        if (! Hash::check($request->string('current_password')->toString(), $user->password)) {
            return back()->withErrors([
                'current_password' => 'كلمة المرور الحالية غير صحيحة.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->string('password')->toString()),
        ]);

        return back()->with('flash', ['success' => 'تم تغيير كلمة المرور بنجاح.']);
    }
}
