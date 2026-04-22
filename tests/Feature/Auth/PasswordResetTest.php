<?php

// AUTH-04: forgot password + password reset

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

// ── Forgot password ─────────────────────────────────────────────────────────

it('تعرض صفحة نسيت كلمة المرور', function () {
    $this->get('/forgot-password')
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Auth/ForgotPassword'));
});

it('يُرسل رابط إعادة التعيين إلى بريد مسجّل', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])
        ->assertRedirect()
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

it('لا يكشف عن وجود البريد الإلكتروني للزوار', function () {
    $this->post('/forgot-password', ['email' => 'notexist@example.com'])
        ->assertSessionHasErrors('email');
});

it('يرفض الطلب بدون بريد إلكتروني', function () {
    $this->post('/forgot-password', [])
        ->assertSessionHasErrors('email');
});

it('يرفض صيغة البريد الإلكتروني غير الصحيحة', function () {
    $this->post('/forgot-password', ['email' => 'not-an-email'])
        ->assertSessionHasErrors('email');
});

// ── Reset password ──────────────────────────────────────────────────────────

it('تعرض صفحة إعادة تعيين كلمة المرور مع الرمز', function () {
    $this->get('/reset-password/some-token')
        ->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page->component('Auth/ResetPassword')
                               ->has('token'),
        );
});

it('يُعيد تعيين كلمة المرور برمز صحيح', function () {
    Notification::fake();

    $user = User::factory()->create(['password' => bcrypt('OldPass1')]);

    $this->post('/forgot-password', ['email' => $user->email]);

    $token = null;
    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
        $token = $notification->token;
        return true;
    });

    $this->post('/reset-password', [
        'token'                 => $token,
        'email'                 => $user->email,
        'password'              => 'NewPass1',
        'password_confirmation' => 'NewPass1',
    ])->assertRedirect(route('login'));

    $this->assertTrue(
        \Illuminate\Support\Facades\Hash::check('NewPass1', $user->fresh()->password),
    );
});

it('يرفض رمز إعادة تعيين منتهي أو خاطئ', function () {
    $user = User::factory()->create();

    $this->post('/reset-password', [
        'token'                 => 'invalid-token',
        'email'                 => $user->email,
        'password'              => 'NewPass1',
        'password_confirmation' => 'NewPass1',
    ])->assertSessionHasErrors('email');
});

it('يُلزم بتطابق كلمتَي المرور', function () {
    $this->post('/reset-password', [
        'token'                 => 'some-token',
        'email'                 => 'user@example.com',
        'password'              => 'NewPass1',
        'password_confirmation' => 'DifferentPass1',
    ])->assertSessionHasErrors('password');
});

it('يُطبّق سياسة كلمة المرور عند الإعادة — AUTH-03', function () {
    $this->post('/reset-password', [
        'token'                 => 'some-token',
        'email'                 => 'user@example.com',
        'password'              => 'weak',
        'password_confirmation' => 'weak',
    ])->assertSessionHasErrors('password');
});
