<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\SeasonalController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SocialAccountController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Landing page ───────────────────────────────────────────────────────────
Route::get('/', function () {
    return Inertia::render('Landing');
})->name('landing');

Route::get('/terms',   fn () => Inertia::render('Terms'))->name('terms');
Route::get('/privacy', fn () => Inertia::render('Privacy'))->name('privacy');

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
        $hasWorkspace = $request->user()->activeWorkspaces()->exists();
        return redirect()->route($hasWorkspace ? 'dashboard' : 'onboarding');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('flash', ['success' => 'تم إرسال رابط التحقق إلى بريدك الإلكتروني.']);
    })->middleware('throttle:6,1')->name('verification.send');
});

// ── Logout ─────────────────────────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ── Authenticated routes ───────────────────────────────────────────────────
Route::middleware(['auth', 'verified', \App\Http\Middleware\RedirectAdminToPanel::class])->group(function () {
    // WS-01: first-run onboarding
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding/workspace', [OnboardingController::class, 'storeWorkspace'])->name('onboarding.workspace');
    Route::post('/onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    // SCH-01→SCH-11: Scheduling — calendar + posts history
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/stats', [PostController::class, 'stats'])->name('posts.stats');
    Route::post('/posts/{post}/reschedule', [PostController::class, 'reschedule'])->name('posts.reschedule');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // SE-01→SE-08: Seasonal engine
    Route::get('/seasonal', [SeasonalController::class, 'index'])->name('seasonal.index');
    Route::get('/seasonal/{key}/generate', [SeasonalController::class, 'generate'])->name('seasonal.generate');

    // CON-01→CON-10: Social account connections
    Route::get('/social/accounts', [SocialAccountController::class, 'index'])->name('social.index');
    Route::get('/social/connect/{provider}', [SocialAccountController::class, 'redirect'])->name('social.redirect');
    Route::get('/social/callback/{provider}', [SocialAccountController::class, 'callback'])->name('social.callback');
    Route::delete('/social/accounts/{account}', [SocialAccountController::class, 'disconnect'])->name('social.disconnect');
    Route::post('/social/accounts/{account}/refresh', [SocialAccountController::class, 'refresh'])->name('social.refresh');

    // ADS-01→ADS-11: Campaigns
    Route::get('campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
    Route::resource('campaigns', CampaignController::class)->except(['edit', 'create']);
    Route::post('campaigns/{campaign}/pause',     [CampaignController::class, 'pause'])->name('campaigns.pause');
    Route::post('campaigns/{campaign}/resume',    [CampaignController::class, 'resume'])->name('campaigns.resume');
    Route::post('campaigns/{campaign}/duplicate', [CampaignController::class, 'duplicate'])->name('campaigns.duplicate');

    // ANL-01→ANL-07: Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/export/pdf', [AnalyticsController::class, 'exportPdf'])->name('analytics.export.pdf');
    Route::get('/analytics/export/csv', [AnalyticsController::class, 'exportCsv'])->name('analytics.export.csv');

    // WS-06, AUTH-07: Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/avatar', [SettingsController::class, 'updateAvatar'])->name('settings.avatar');

    // Notifications
    Route::post('/notifications/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // Web Push subscriptions
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::delete('/push/subscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    // BIL-01→BIL-08: Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('/billing/callback', [BillingController::class, 'callback'])->name('billing.callback');
    Route::get('/billing/invoices/{invoice}/download', [BillingController::class, 'downloadInvoice'])->name('billing.invoice.download');
});

// ── Payment gateway webhooks (no CSRF, no auth) ────────────────────────────
Route::post('/webhooks/moyasar', [BillingController::class, 'webhook'])->name('billing.webhook');

// ── Stop impersonation — accessible while logged in as non-admin user ────────
Route::post('/admin/impersonate/stop', [\App\Http\Controllers\Admin\AdminUserController::class, 'stopImpersonating'])
    ->middleware(['auth'])
    ->name('admin.impersonate.stop');

// ── Admin Dashboard (Phase 0.6 — M8) ──────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::get('/',                                  [\App\Http\Controllers\Admin\AdminDashboardController::class,    'index'])->name('dashboard');
    Route::get('/users',                             [\App\Http\Controllers\Admin\AdminUserController::class,         'index'])->name('users.index');
    Route::get('/users/{user}',                      [\App\Http\Controllers\Admin\AdminUserController::class,         'show'])->name('users.show');
    Route::post('/users/{user}/ban',                 [\App\Http\Controllers\Admin\AdminUserController::class,         'ban'])->name('users.ban');
    Route::post('/users/{user}/unban',               [\App\Http\Controllers\Admin\AdminUserController::class,         'unban'])->name('users.unban');
    Route::post('/users/{user}/grant-tokens',        [\App\Http\Controllers\Admin\AdminUserController::class,         'grantTokens'])->name('users.grant-tokens');
    Route::post('/users/{user}/impersonate',         [\App\Http\Controllers\Admin\AdminUserController::class,         'impersonate'])->name('users.impersonate');
    Route::get('/workspaces',                        [\App\Http\Controllers\Admin\AdminWorkspaceController::class,    'index'])->name('workspaces.index');
    Route::post('/workspaces/{workspace}/suspend',   [\App\Http\Controllers\Admin\AdminWorkspaceController::class,   'suspend'])->name('workspaces.suspend');
    Route::post('/workspaces/{workspace}/restore',   [\App\Http\Controllers\Admin\AdminWorkspaceController::class,   'restore'])->name('workspaces.restore');
    Route::get('/posts',                             [\App\Http\Controllers\Admin\AdminPostsController::class,        'index'])->name('posts.index');
    Route::get('/social-accounts',                   [\App\Http\Controllers\Admin\AdminSocialAccountsController::class, 'index'])->name('social-accounts.index');
    Route::get('/ai-generations',                    [\App\Http\Controllers\Admin\AdminAiGenerationsController::class,  'index'])->name('ai-generations.index');
    Route::get('/campaigns',                         [\App\Http\Controllers\Admin\AdminCampaignController::class,       'index'])->name('campaigns.index');
    Route::post('/campaigns/{campaign}/force-status', [\App\Http\Controllers\Admin\AdminCampaignController::class,      'forceStatus'])->name('campaigns.force-status');
    Route::get('/billing',                            [\App\Http\Controllers\Admin\AdminBillingController::class,        'index'])->name('billing.index');
    Route::get('/billing/export',                    [\App\Http\Controllers\Admin\AdminBillingController::class,        'export'])->name('billing.export');
    Route::get('/tokens',                             [\App\Http\Controllers\Admin\AdminTokenAuditController::class,     'index'])->name('tokens.index');
    Route::get('/tokens/export',                     [\App\Http\Controllers\Admin\AdminTokenAuditController::class,     'export'])->name('tokens.export');
    Route::get('/ai-costs',                           [\App\Http\Controllers\Admin\AdminAiCostController::class,         'index'])->name('ai-costs.index');
    Route::get('/notifications',                      [\App\Http\Controllers\Admin\AdminNotificationController::class,   'index'])->name('notifications.index');
    Route::post('/notifications/broadcast',           [\App\Http\Controllers\Admin\AdminNotificationController::class,   'broadcast'])->name('notifications.broadcast');
    Route::get('/system',                             [\App\Http\Controllers\Admin\AdminSystemController::class,         'index'])->name('system.index');
    Route::post('/system/clear-cache',                [\App\Http\Controllers\Admin\AdminSystemController::class,         'clearCache'])->name('system.clear-cache');
    Route::post('/system/optimize',                   [\App\Http\Controllers\Admin\AdminSystemController::class,         'optimize'])->name('system.optimize');
    Route::get('/settings',                           [\App\Http\Controllers\Admin\AdminSettingsController::class,       'index'])->name('settings.index');
    Route::post('/settings/flags',                    [\App\Http\Controllers\Admin\AdminSettingsController::class,       'updateFlags'])->name('settings.flags');
    Route::post('/settings/packages/{package}',       [\App\Http\Controllers\Admin\AdminSettingsController::class,       'updatePackage'])->name('settings.packages.update');
});

// ── Artisan helper — protected by secret key (cPanel deployments) ──────────
Route::get('/up/storage-link', function () {
    abort_unless(request('secret') === config('app.artisan_secret'), 403);
    Illuminate\Support\Facades\Artisan::call('storage:link');
    return response('✓ ' . trim(Illuminate\Support\Facades\Artisan::output()));
});

Route::get('/up/migrate', function () {
    abort_unless(request('secret') === config('app.artisan_secret'), 403);
    Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return response('✓ ' . trim(Illuminate\Support\Facades\Artisan::output()));
});
