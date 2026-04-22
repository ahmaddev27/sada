<?php

// SCH-01: posts can be scheduled via SavePostAction
// SCH-02: DispatchDuePosts command finds due posts
// SCH-03: PublishPostJob dispatched to queue
// SCH-07: calendar + history pages render
// SCH-08: reschedule + delete

use App\Jobs\PublishPostJob;
use App\Models\Post;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Queue;

function makeSchedulingWorkspace(): array
{
    $user      = User::factory()->create(['token_balance' => 200]);
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    return [$user, $workspace];
}

// ── SCH-07: Calendar page ────────────────────────────────────

it('تعرض صفحة التقويم للمستخدم المُسجَّل', function () {
    [$user] = makeSchedulingWorkspace();

    $response = $this->actingAs($user)->get('/calendar');

    $response->assertOk()
        ->assertInertia(fn ($p) => $p->component('Calendar/Index'));
});

it('تعرض صفحة التقويم منشورات مجدولة للشهر الحالي', function () {
    [$user, $workspace] = makeSchedulingWorkspace();

    Post::factory()->for($workspace)->create([
        'user_id'       => $user->id,
        'status'        => 'scheduled',
        'scheduled_for' => now()->addDay(),
    ]);

    $response = $this->actingAs($user)
        ->get('/calendar?year=' . now()->year . '&month=' . now()->month);

    $response->assertOk()
        ->assertInertia(fn ($p) => $p
            ->component('Calendar/Index')
            ->has('posts', 1)
        );
});

// ── SCH-07: Posts history page ───────────────────────────────

it('تعرض صفحة سجل المحتوى', function () {
    [$user, $workspace] = makeSchedulingWorkspace();

    Post::factory()->for($workspace)->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get('/posts');

    $response->assertOk()
        ->assertInertia(fn ($p) => $p
            ->component('Posts/Index')
            ->has('posts')
        );
});

it('يدعم الفلترة بالحالة في صفحة السجل', function () {
    [$user, $workspace] = makeSchedulingWorkspace();

    Post::factory()->for($workspace)->create(['user_id' => $user->id, 'status' => 'draft']);
    Post::factory()->for($workspace)->create(['user_id' => $user->id, 'status' => 'published', 'published_at' => now()]);

    $response = $this->actingAs($user)->get('/posts?status=draft');

    $response->assertOk()
        ->assertInertia(fn ($p) => $p
            ->where('posts.total', 1)
            ->where('posts.data.0.status', 'draft')
        );
});

// ── SCH-02: DispatchDuePosts command ────────────────────────

it('يُرسل PublishPostJob للمنشورات التي حان وقتها', function () {
    Queue::fake();

    [$user, $workspace] = makeSchedulingWorkspace();

    // Due post
    Post::factory()->for($workspace)->create([
        'user_id'       => $user->id,
        'status'        => 'scheduled',
        'scheduled_for' => now()->subMinute(),
    ]);

    // Future post — should NOT be dispatched
    Post::factory()->for($workspace)->create([
        'user_id'       => $user->id,
        'status'        => 'scheduled',
        'scheduled_for' => now()->addHour(),
    ]);

    $this->artisan('posts:dispatch-due')->assertSuccessful();

    Queue::assertPushed(PublishPostJob::class, 1);
});

it('لا يُرسل منشورات غير مجدولة', function () {
    Queue::fake();

    [$user, $workspace] = makeSchedulingWorkspace();

    Post::factory()->for($workspace)->create([
        'user_id' => $user->id,
        'status'  => 'draft',
    ]);

    $this->artisan('posts:dispatch-due')->assertSuccessful();

    Queue::assertNothingPushed();
});

// ── SCH-08: Reschedule ───────────────────────────────────────

it('يُعيد جدولة المنشور بتاريخ جديد', function () {
    [$user, $workspace] = makeSchedulingWorkspace();

    $post = Post::factory()->for($workspace)->create([
        'user_id'       => $user->id,
        'status'        => 'scheduled',
        'scheduled_for' => now()->addDay(),
    ]);

    $newDate = now()->addDays(3)->toDateTimeString();

    $response = $this->actingAs($user)
        ->post("/posts/{$post->id}/reschedule", ['scheduled_for' => $newDate]);

    $response->assertRedirect();

    expect($post->fresh()->status)->toBe('scheduled');
    expect($post->fresh()->scheduled_for)->not->toBeNull();
});

// ── SCH-08: Delete ───────────────────────────────────────────

it('يحذف المنشور ويُعيد JSON', function () {
    [$user, $workspace] = makeSchedulingWorkspace();

    $post = Post::factory()->for($workspace)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->deleteJson("/posts/{$post->id}");

    $response->assertOk()
        ->assertJsonFragment(['message' => 'تم الحذف.']);

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

it('يمنع المستخدم غير المُصرَّح من حذف منشور شخص آخر', function () {
    [$user, $workspace] = makeSchedulingWorkspace();
    [$otherUser]        = makeSchedulingWorkspace();

    $post = Post::factory()->for($workspace)->create(['user_id' => $user->id]);

    $response = $this->actingAs($otherUser)
        ->deleteJson("/posts/{$post->id}");

    $response->assertForbidden();
    $this->assertDatabaseHas('posts', ['id' => $post->id]);
});
