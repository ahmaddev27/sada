<?php

// SCH-03: PublishPostJob marks post published and notifies user
// SCH-07: PostPublishedNotification sent on success
// SCH-07: PostFailedNotification sent on final failure
// SCH-06: failed() hook fires notification on exhausted retries

use App\Jobs\PublishPostJob;
use App\Models\Post;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\PostFailedNotification;
use App\Notifications\PostPublishedNotification;
use App\Services\Meta\MetaPublishingService;
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;

function makeJobWorld(): array
{
    $user      = User::factory()->create(['token_balance' => 200]);
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $account   = SocialAccount::factory()->instagram()->for($workspace)->create();

    $post = Post::factory()->for($workspace)->create([
        'user_id'          => $user->id,
        'status'           => 'scheduled',
        'social_account_id' => $account->id,
        'scheduled_for'    => now()->subMinute(),
    ]);

    return [$user, $workspace, $account, $post];
}

// ── Success path ─────────────────────────────────────────────────────────────

it('يُعلّم المنشور كمنشور ويُرسل إشعار النجاح', function () {
    Notification::fake();

    [, , , $post] = makeJobWorld();

    $this->mock(MetaPublishingService::class, function (MockInterface $mock) {
        $mock->shouldReceive('publish')->once()->andReturn('ext-ig-999');
    });

    (new PublishPostJob($post))->handle(app(MetaPublishingService::class));

    expect($post->fresh()->status)->toBe('published');
    expect($post->fresh()->published_at)->not->toBeNull();
    expect($post->fresh()->metadata['external_id'])->toBe('ext-ig-999');

    Notification::assertSentTo(
        $post->user,
        PostPublishedNotification::class,
    );
});

it('لا يُعيد نشر منشور ليس في حالة مجدول', function () {
    Notification::fake();

    [, , , $post] = makeJobWorld();
    $post->update(['status' => 'published']);

    $this->mock(MetaPublishingService::class, function (MockInterface $mock) {
        $mock->shouldReceive('publish')->never();
    });

    (new PublishPostJob($post))->handle(app(MetaPublishingService::class));

    Notification::assertNothingSent();
});

// ── No social account ────────────────────────────────────────────────────────

it('يُعلّم المنشور كفاشل ويُرسل إشعار الفشل إذا لم يكن هناك حساب اجتماعي', function () {
    Notification::fake();

    [$user, $workspace] = makeJobWorld();
    $post = Post::factory()->for($workspace)->create([
        'user_id'           => $user->id,
        'status'            => 'scheduled',
        'social_account_id' => null,
        'scheduled_for'     => now()->subMinute(),
    ]);

    $this->mock(MetaPublishingService::class, function (MockInterface $mock) {
        $mock->shouldReceive('publish')->never();
    });

    (new PublishPostJob($post))->handle(app(MetaPublishingService::class));

    expect($post->fresh()->status)->toBe('failed');
    expect($post->fresh()->metadata['error'])->toContain('No social account linked');

    Notification::assertSentTo($user, PostFailedNotification::class);
});

// ── Failure path (final attempt) ─────────────────────────────────────────────

it('يُعلّم المنشور كفاشل ويُرسل إشعار عند فشل النشر في المحاولة الأخيرة', function () {
    Notification::fake();

    [, , , $post] = makeJobWorld();

    $this->mock(MetaPublishingService::class, function (MockInterface $mock) {
        $mock->shouldReceive('publish')->once()->andThrow(new RuntimeException('فشل الاتصال بـ Meta API'));
    });

    $job = new PublishPostJob($post);

    // Simulate final attempt (attempts() returns tries)
    $job->tries = 1;

    try {
        $job->handle(app(MetaPublishingService::class));
    } catch (Throwable) {
        // Expected — job re-throws so Laravel can handle retries
    }

    expect($post->fresh()->status)->toBe('failed');
    expect($post->fresh()->metadata['error'])->toContain('فشل الاتصال بـ Meta API');

    Notification::assertSentTo($post->user, PostFailedNotification::class);
});

// ── failed() hook ────────────────────────────────────────────────────────────

it('يُرسل إشعار الفشل عبر failed() إذا لم يكن المنشور قد عُلّم بالفشل', function () {
    Notification::fake();

    [, , , $post] = makeJobWorld();

    $job = new PublishPostJob($post);
    $job->failed(new RuntimeException('انتهت كل المحاولات'));

    expect($post->fresh()->status)->toBe('failed');

    Notification::assertSentTo($post->user, PostFailedNotification::class);
});

it('لا يُرسل إشعاراً مكرراً من failed() إذا كان المنشور فاشلاً بالفعل', function () {
    Notification::fake();

    [, , , $post] = makeJobWorld();
    $post->update(['status' => 'failed']);

    $job = new PublishPostJob($post);
    $job->failed(new RuntimeException('انتهت المحاولات'));

    Notification::assertNothingSent();
});
