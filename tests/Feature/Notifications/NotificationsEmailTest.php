<?php

// SCH-07, BIL-04, AUTH-01: email channel on all notifications

use App\Models\Post;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\LowTokenBalanceNotification;
use App\Notifications\PostFailedNotification;
use App\Notifications\PostPublishedNotification;
use App\Notifications\WelcomeNotification;
use App\Services\TokenService;
use Illuminate\Support\Facades\Notification;

function makeNotifWorld(): array
{
    $user      = User::factory()->create(['name' => 'أحمد', 'token_balance' => 200]);
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $account   = SocialAccount::factory()->instagram()->for($workspace)->create();
    $post      = Post::factory()->for($workspace)->create([
        'user_id'          => $user->id,
        'social_account_id'=> $account->id,
        'platform'         => 'instagram',
    ]);

    return [$user, $post];
}

// ── via() includes mail ───────────────────────────────────────────────────────

it('PostPublishedNotification تُرسل عبر database وmail', function () {
    [, $post] = makeNotifWorld();
    $notif = new PostPublishedNotification($post);
    expect($notif->via(new \stdClass()))->toContain('mail')->toContain('database');
});

it('PostFailedNotification تُرسل عبر database وmail', function () {
    [, $post] = makeNotifWorld();
    $notif = new PostFailedNotification($post, 'خطأ في الاتصال');
    expect($notif->via(new \stdClass()))->toContain('mail')->toContain('database');
});

it('WelcomeNotification تُرسل عبر database وmail', function () {
    $notif = new WelcomeNotification();
    expect($notif->via(new \stdClass()))->toContain('mail')->toContain('database');
});

it('LowTokenBalanceNotification تُرسل عبر database وmail', function () {
    $notif = new LowTokenBalanceNotification(45);
    expect($notif->via(new \stdClass()))->toContain('mail')->toContain('database');
});

// ── toMail() content ─────────────────────────────────────────────────────────

it('PostPublishedNotification تحتوي على اسم المنصة في عنوان الإيميل', function () {
    [$user, $post] = makeNotifWorld();
    $mail = (new PostPublishedNotification($post))->toMail($user);
    expect($mail->subject)->toContain('انستجرام');
});

it('PostFailedNotification تحتوي على سبب الفشل في الإيميل', function () {
    [$user, $post] = makeNotifWorld();
    $mail = (new PostFailedNotification($post, 'انتهت صلاحية الرمز'))->toMail($user);
    $bodyText = collect($mail->introLines)->implode(' ');
    expect($bodyText)->toContain('انتهت صلاحية الرمز');
});

it('PostFailedNotification بدون سبب لا تحتوي على كلمة السبب', function () {
    [$user, $post] = makeNotifWorld();
    $mail = (new PostFailedNotification($post))->toMail($user);
    $bodyText = collect($mail->introLines)->implode(' ');
    expect($bodyText)->not->toContain('السبب:');
});

it('LowTokenBalanceNotification تحتوي على رقم الرصيد في الإيميل', function () {
    [$user] = makeNotifWorld();
    $mail = (new LowTokenBalanceNotification(45))->toMail($user);
    $bodyText = collect($mail->introLines)->implode(' ');
    expect($bodyText)->toContain('45');
});

// ── BIL-04: low balance threshold trigger ────────────────────────────────────

it('TokenService يُرسل إشعار انخفاض الرصيد عند عبور الحد', function () {
    Notification::fake();

    $user = User::factory()->create(['token_balance' => 120]);

    app(TokenService::class)->deduct($user, 40, 'اختبار');

    // Balance is now 80 (< 100 threshold), was 120 (>= 100) — should notify
    Notification::assertSentTo($user, LowTokenBalanceNotification::class);
});

it('TokenService لا يُرسل إشعار انخفاض الرصيد إذا كان الرصيد منخفضاً أصلاً', function () {
    Notification::fake();

    $user = User::factory()->create(['token_balance' => 80]);

    app(TokenService::class)->deduct($user, 20, 'اختبار');

    // Was already below threshold — no notification
    Notification::assertNotSentTo($user, LowTokenBalanceNotification::class);
});

it('TokenService لا يُرسل إشعار إذا بقي الرصيد فوق الحد', function () {
    Notification::fake();

    $user = User::factory()->create(['token_balance' => 200]);

    app(TokenService::class)->deduct($user, 40, 'اختبار');

    // Balance is now 160 — still above threshold
    Notification::assertNotSentTo($user, LowTokenBalanceNotification::class);
});

// ── toArray() data ────────────────────────────────────────────────────────────

it('LowTokenBalanceNotification تحتوي على الرصيد في toArray', function () {
    $data = (new LowTokenBalanceNotification(45))->toArray(new \stdClass());
    expect($data['type'])->toBe('low_balance');
    expect($data['balance'])->toBe(45);
});
