<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\SocialAccountController;
use App\Http\Controllers\WorkspaceController;
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

    // WS-01 → WS-05
    Route::get('/workspaces', [WorkspaceController::class, 'index'])->name('workspace.index');
    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspace.store');
    Route::get('/workspaces/{workspace}/settings', [WorkspaceController::class, 'settings'])->name('workspace.settings');
    Route::post('/workspaces/{workspace}', [WorkspaceController::class, 'update'])->name('workspace.update');
    Route::post('/workspaces/{workspace}/brand', [WorkspaceController::class, 'updateBrand'])->name('workspace.brand');
    Route::post('/workspaces/{workspace}/switch', [WorkspaceController::class, 'switch'])->name('workspace.switch');
    Route::post('/workspaces/{workspace}/archive', [WorkspaceController::class, 'archive'])->name('workspace.archive');

    // CG-01→CG-11: Content generation
    Route::get('/generate', [GenerateController::class, 'index'])->name('generate.index');
    Route::post('/generate', [GenerateController::class, 'generate'])->name('generate.generate');
    Route::post('/generate/save', [GenerateController::class, 'save'])->name('generate.save');

    // CON-01→CON-10: Social account connections
    Route::get('/social/accounts', [SocialAccountController::class, 'index'])->name('social.index');
    Route::get('/social/connect/{provider}', [SocialAccountController::class, 'redirect'])->name('social.redirect');
    Route::get('/social/callback/{provider}', [SocialAccountController::class, 'callback'])->name('social.callback');
    Route::delete('/social/accounts/{account}', [SocialAccountController::class, 'disconnect'])->name('social.disconnect');
    Route::post('/social/accounts/{account}/refresh', [SocialAccountController::class, 'refresh'])->name('social.refresh');
});
