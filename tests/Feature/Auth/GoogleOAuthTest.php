<?php

// AUTH-02: Google OAuth login via Socialite

use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

function mockGoogleUser(
    string $id = '123456789',
    string $name = 'محمد الغامدي',
    string $email = 'mohammed@gmail.com',
): SocialiteUser {
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->allows('getId')->andReturn($id);
    $socialiteUser->allows('getName')->andReturn($name);
    $socialiteUser->allows('getEmail')->andReturn($email);

    return $socialiteUser;
}

beforeEach(function () {
    Socialite::shouldReceive('driver->user')
        ->andReturn(mockGoogleUser());
});

it('يُنشئ مستخدماً جديداً عبر Google OAuth', function () {
    Socialite::shouldReceive('driver->redirect')->andReturnSelf();

    // New user has no workspace → redirects to onboarding
    $this->get('/auth/google/callback')
        ->assertRedirect(route('onboarding'));

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'email'     => 'mohammed@gmail.com',
        'google_id' => '123456789',
    ]);
});

it('يتحقق من البريد الإلكتروني تلقائياً عند التسجيل بـ Google', function () {
    $this->get('/auth/google/callback');

    $user = User::where('email', 'mohammed@gmail.com')->first();

    expect($user->email_verified_at)->not->toBeNull();
});

it('يُسجّل دخول مستخدم موجود مسبقاً بـ google_id', function () {
    $existing = User::factory()->create([
        'email'     => 'mohammed@gmail.com',
        'google_id' => '123456789',
    ]);

    // Existing user with a workspace → redirects to dashboard
    $existing->workspaces()->create([
        'name'            => 'متجر الاختبار',
        'countries'       => ['sa'],
        'default_dialect' => 'sa',
    ]);

    $this->get('/auth/google/callback')
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($existing);
    $this->assertDatabaseCount('users', 1);
});

it('يربط حساب Google بمستخدم موجود بنفس البريد', function () {
    $existing = User::factory()->create([
        'email'     => 'mohammed@gmail.com',
        'google_id' => null,
    ]);

    $this->get('/auth/google/callback');

    $existing->refresh();

    expect($existing->google_id)->toBe('123456789');
    $this->assertDatabaseCount('users', 1);
});

it('يُعيّن رصيد الرموز صفراً عند إنشاء حساب Google جديد', function () {
    $this->get('/auth/google/callback');

    $user = User::where('email', 'mohammed@gmail.com')->first();

    expect($user->token_balance)->toBe(0);
});

it('يرفض المستخدم المسجّل الوصول إلى مسار OAuth', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/auth/google')
        ->assertRedirect();
});
