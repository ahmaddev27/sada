<?php

// AUTH-05: login with remember me
// AUTH-06: rate limiting

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

// ── Login form ─────────────────────────────────────────────────────────────

it('تعرض صفحة تسجيل الدخول', function () {
    $this->get('/login')->assertStatus(200)->assertInertia(
        fn ($page) => $page->component('Auth/Login'),
    );
});

it('يرفض الوصول المصادق لصفحة تسجيل الدخول', function () {
    $this->actingAs(User::factory()->create())
        ->get('/login')
        ->assertRedirect();
});

// ── Successful login ────────────────────────────────────────────────────────

it('يُسجّل دخول المستخدم بالبريد الإلكتروني وكلمة المرور الصحيحَين', function () {
    $user = User::factory()->create(['password' => bcrypt('Password1')]);

    $this->post('/login', ['email' => $user->email, 'password' => 'Password1'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('يُعيد توجيه المستخدم بعد تسجيل الدخول إلى الـ intended URL', function () {
    $user = User::factory()->create(['password' => bcrypt('Password1')]);

    $this->get('/dashboard'); // sets intended URL
    $this->post('/login', ['email' => $user->email, 'password' => 'Password1'])
        ->assertRedirect(route('dashboard'));
});

it('يُطبّق remember me — AUTH-05', function () {
    $user = User::factory()->create(['password' => bcrypt('Password1')]);

    $this->post('/login', [
        'email'    => $user->email,
        'password' => 'Password1',
        'remember' => true,
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

// ── Validation ──────────────────────────────────────────────────────────────

it('يرفض بيانات الدخول غير الصحيحة', function () {
    $user = User::factory()->create(['password' => bcrypt('Password1')]);

    $this->post('/login', ['email' => $user->email, 'password' => 'wrongpassword'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('يرفض المستخدم غير الموجود', function () {
    $this->post('/login', ['email' => 'nouser@example.com', 'password' => 'Password1'])
        ->assertSessionHasErrors('email');
});

it('يُلزم بإدخال البريد الإلكتروني وكلمة المرور', function () {
    $this->post('/login', [])
        ->assertSessionHasErrors(['email', 'password']);
});

// ── Rate limiting (AUTH-06) ─────────────────────────────────────────────────

it('يحجب المستخدم بعد 5 محاولات فاشلة — AUTH-06', function () {
    $user = User::factory()->create(['password' => bcrypt('Password1')]);

    foreach (range(1, 5) as $_) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrongpass']);
    }

    $this->post('/login', ['email' => $user->email, 'password' => 'Password1'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('يُفرج عن القيد بعد تسجيل الدخول الناجح', function () {
    $user = User::factory()->create(['password' => bcrypt('Password1')]);

    RateLimiter::hit(
        \Illuminate\Support\Str::transliterate(
            \Illuminate\Support\Str::lower($user->email) . '|127.0.0.1',
        ),
        900,
    );

    $this->post('/login', ['email' => $user->email, 'password' => 'Password1'])
        ->assertRedirect(route('dashboard'));
});

// ── Logout ──────────────────────────────────────────────────────────────────

it('يُسجّل خروج المستخدم المصادق', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('يُبطل الجلسة عند تسجيل الخروج', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/logout');

    $this->assertGuest();
});
