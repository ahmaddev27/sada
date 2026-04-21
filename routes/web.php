<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Landing page ───────────────────────────────────────────────────────────
Route::get('/', function () {
    return Inertia::render('Landing');
})->name('landing');

// ── Guest routes ───────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    // AUTH-01
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // AUTH-05, AUTH-06
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // AUTH-04: forgot + reset password
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

    // AUTH-02: Google OAuth
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

// ── Email verification (AUTH-01) ───────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return Inertia::render('Auth/VerifyEmail');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('dashboard');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('flash', ['success' => 'تم إرسال رابط التحقق إلى بريدك الإلكتروني.']);
    })->middleware('throttle:6,1')->name('verification.send');
});

// ── Logout ─────────────────────────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ── Authenticated routes ───────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Index');
    })->name('dashboard');
});
