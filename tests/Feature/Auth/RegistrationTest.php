<?php

// AUTH-01: email/password registration with email verification
// AUTH-03: password policy (min 8 chars, uppercase, number)

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake([Registered::class]);
});

it('يعرض صفحة التسجيل', function () {
    $this->get('/register')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Auth/Register'));
});

it('يسجّل مستخدماً جديداً بنجاح', function () {
    $this->post('/register', [
        'name'                  => 'أحمد الغامدي',
        'email'                 => 'ahmed@example.com',
        'password'              => 'Password123',
        'password_confirmation' => 'Password123',
    ])->assertRedirect('/email/verify');

    $this->assertDatabaseHas('users', [
        'name'          => 'أحمد الغامدي',
        'email'         => 'ahmed@example.com',
        'token_balance' => 0,
    ]);

    Event::assertDispatched(Registered::class);
});

it('يُسجّل دخول المستخدم تلقائياً بعد التسجيل', function () {
    $this->post('/register', [
        'name'                  => 'فاطمة الزهراني',
        'email'                 => 'fatima@example.com',
        'password'              => 'SecurePass9',
        'password_confirmation' => 'SecurePass9',
    ]);

    $this->assertAuthenticated();
});

it('يرفض التسجيل بدون اسم', function () {
    $this->post('/register', [
        'name'                  => '',
        'email'                 => 'test@example.com',
        'password'              => 'Password123',
        'password_confirmation' => 'Password123',
    ])->assertInvalid(['name']);
});

it('يرفض البريد الإلكتروني المكرر', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $this->post('/register', [
        'name'                  => 'محمد العتيبي',
        'email'                 => 'existing@example.com',
        'password'              => 'Password123',
        'password_confirmation' => 'Password123',
    ])->assertInvalid(['email']);
});

it('يرفض البريد الإلكتروني بصيغة غير صحيحة', function () {
    $this->post('/register', [
        'name'                  => 'نورة السعيد',
        'email'                 => 'not-an-email',
        'password'              => 'Password123',
        'password_confirmation' => 'Password123',
    ])->assertInvalid(['email']);
});

// AUTH-03: password policy
it('يرفض كلمة المرور أقل من 8 أحرف', function () {
    $this->post('/register', [
        'name'                  => 'خالد المطيري',
        'email'                 => 'khalid@example.com',
        'password'              => 'Ab1',
        'password_confirmation' => 'Ab1',
    ])->assertInvalid(['password']);
});

it('يرفض كلمة المرور بدون حرف كبير', function () {
    $this->post('/register', [
        'name'                  => 'سارة الدوسري',
        'email'                 => 'sara@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ])->assertInvalid(['password']);
});

it('يرفض كلمة المرور بدون أرقام', function () {
    $this->post('/register', [
        'name'                  => 'عبدالله القحطاني',
        'email'                 => 'abdulla@example.com',
        'password'              => 'PasswordOnly',
        'password_confirmation' => 'PasswordOnly',
    ])->assertInvalid(['password']);
});

it('يرفض كلمات المرور غير المتطابقة', function () {
    $this->post('/register', [
        'name'                  => 'ريم الشهري',
        'email'                 => 'reem@example.com',
        'password'              => 'Password123',
        'password_confirmation' => 'Different456',
    ])->assertInvalid(['password']);
});

it('يحوّل البريد الإلكتروني إلى أحرف صغيرة تلقائياً', function () {
    $this->post('/register', [
        'name'                  => 'يوسف الحربي',
        'email'                 => 'Yusuf@EXAMPLE.COM',
        'password'              => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $this->assertDatabaseHas('users', ['email' => 'yusuf@example.com']);
});

it('يرفض المستخدم المسجّل الوصول إلى صفحة التسجيل', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/register')
        ->assertRedirect();
});
