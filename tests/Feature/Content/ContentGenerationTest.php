<?php

// CG-05: generate 3 variations via AI
// CG-08: save as draft / schedule / publish
// CG-10: token balance enforcement

use App\Models\AiGeneration;
use App\Models\Post;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Ai\ContentGenerationService;
use Mockery\MockInterface;

// ── Helpers ─────────────────────────────────────────────────────────────────

function makeWorkspace(int $tokenBalance = 200): array
{
    $user      = User::factory()->create(['token_balance' => $tokenBalance]);
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    return [$user, $workspace];
}

function fakeVariations(): array
{
    return [
        ['title' => 'الخيار ١ · إبداعي', 'body' => 'نص تجريبي أول — جودة عالية وأسعار منافسة', 'tags' => ['#تسويق', '#منتجات'], 'char_count' => 42],
        ['title' => 'الخيار ٢ · رسمي',   'body' => 'نص تجريبي ثانٍ — خدمة احترافية لعملائنا',  'tags' => ['#خدمة', '#جودة'],   'char_count' => 41],
        ['title' => 'الخيار ٣ · عفوي',   'body' => 'نص تجريبي ثالث — تجربة لا تُنسى تنتظرك',  'tags' => ['#تجربة', '#إبداع'], 'char_count' => 41],
    ];
}

// ── CG-05: Generation endpoint returns 3 variations ─────────────────────────

it('يُولّد ثلاثة خيارات محتوى عبر الـ AI', function () {
    [$user, $workspace] = makeWorkspace(200);

    $this->mock(ContentGenerationService::class, function (MockInterface $mock) {
        $mock->shouldReceive('generate')->once()->andReturn(fakeVariations());
    });

    $response = $this->actingAs($user)
        ->postJson('/generate', [
            'content_type' => 'post',
            'platform'     => 'instagram',
            'dialect'      => 'sa',
            'prompt'       => 'اكتب عن افتتاح مطعم جديد في الرياض',
        ]);

    $response->assertOk()
        ->assertJsonStructure([
            'variations' => [
                '*' => ['title', 'body', 'tags', 'char_count'],
            ],
            'tokens_charged',
        ]);

    expect($response->json('variations'))->toHaveCount(3);
    expect($response->json('tokens_charged'))->toBe(40);
});

it('يُسجّل سجل ai_generation بعد التوليد', function () {
    [$user, $workspace] = makeWorkspace(200);

    $this->mock(ContentGenerationService::class, function (MockInterface $mock) {
        $mock->shouldReceive('generate')->once()->andReturn(fakeVariations());
    });

    $this->actingAs($user)
        ->postJson('/generate', [
            'content_type' => 'reel',
            'platform'     => 'tiktok',
            'dialect'      => 'ae',
            'prompt'       => 'إعلان عن خصم موسم الصيف',
        ]);

    $this->assertDatabaseHas('ai_generations', [
        'workspace_id'        => $workspace->id,
        'user_id'             => $user->id,
        'agent_type'          => 'content_generator',
        'platform'            => 'tiktok',
        'dialect'             => 'ae',
        'sada_tokens_charged' => 40,
    ]);
});

// ── CG-10: Token balance enforcement ────────────────────────────────────────

it('يرفض التوليد إذا كان الرصيد أقل من 40 توكن', function () {
    [$user, $workspace] = makeWorkspace(tokenBalance: 10);

    $this->mock(ContentGenerationService::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('generate');
    });

    $response = $this->actingAs($user)
        ->postJson('/generate', [
            'content_type' => 'post',
            'platform'     => 'instagram',
            'dialect'      => 'fos',
            'prompt'       => 'اكتب منشوراً تجريبياً',
        ]);

    $response->assertStatus(422)
        ->assertJsonFragment(['error' => 'رصيد التوكنز غير كافٍ. يرجى شحن المزيد.']);
});

it('يخصم 40 توكن من رصيد المستخدم بعد التوليد', function () {
    [$user, $workspace] = makeWorkspace(200);

    $this->mock(ContentGenerationService::class, function (MockInterface $mock) {
        $mock->shouldReceive('generate')->once()->andReturn(fakeVariations());
    });

    $this->actingAs($user)
        ->postJson('/generate', [
            'content_type' => 'post',
            'platform'     => 'facebook',
            'dialect'      => 'kw',
            'prompt'       => 'ترويج لعرض رمضان الخاص',
        ]);

    expect($user->fresh()->token_balance)->toBe(160);
});

// ── CG-08: Save as draft ─────────────────────────────────────────────────────

it('يحفظ المحتوى المُختار كمسودة', function () {
    [$user, $workspace] = makeWorkspace(200);

    $response = $this->actingAs($user)
        ->post('/generate/save', [
            'content'      => 'نص المنشور المُختار لمطعم الرياض',
            'hashtags'     => ['#رياض', '#مطعم'],
            'platform'     => 'instagram',
            'content_type' => 'post',
            'dialect'      => 'sa',
            'action'       => 'draft',
        ]);

    $response->assertRedirect('/generate');

    $this->assertDatabaseHas('posts', [
        'workspace_id'  => $workspace->id,
        'user_id'       => $user->id,
        'platform'      => 'instagram',
        'content_type'  => 'post',
        'status'        => 'draft',
        'dialect'       => 'sa',
    ]);
});

it('يحفظ المنشور مجدولاً بتاريخ مستقبلي', function () {
    [$user, $workspace] = makeWorkspace(200);

    $scheduledFor = now()->addDay()->toDateTimeString();

    $response = $this->actingAs($user)
        ->post('/generate/save', [
            'content'       => 'منشور مجدول لإعلان الافتتاح',
            'hashtags'      => ['#افتتاح'],
            'platform'      => 'facebook',
            'content_type'  => 'post',
            'dialect'       => 'fos',
            'action'        => 'schedule',
            'scheduled_for' => $scheduledFor,
        ]);

    $response->assertRedirect('/generate');

    $post = Post::where('workspace_id', $workspace->id)->latest()->first();
    expect($post)->not->toBeNull();
    expect($post->status)->toBe('scheduled');
    expect($post->scheduled_for)->not->toBeNull();
});

// ── CG-01: Generation page renders with social accounts ─────────────────────

it('تعرض صفحة التوليد للمستخدم المُسجَّل', function () {
    [$user, $workspace] = makeWorkspace();

    $response = $this->actingAs($user)->get('/generate');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('Generate/Index'));
});

it('يرفض الوصول لغير المُسجَّلين', function () {
    $response = $this->get('/generate');
    $response->assertRedirect('/login');
});

// ── Validation ───────────────────────────────────────────────────────────────

it('يرفض التوليد بدون حقل prompt', function () {
    [$user] = makeWorkspace();

    $response = $this->actingAs($user)
        ->postJson('/generate', [
            'content_type' => 'post',
            'platform'     => 'instagram',
            'dialect'      => 'sa',
        ]);

    $response->assertStatus(422);
});

it('يرفض حفظ منشور بمنصة غير مدعومة', function () {
    [$user] = makeWorkspace();

    $response = $this->actingAs($user)
        ->post('/generate/save', [
            'content'      => 'نص المنشور',
            'platform'     => 'twitter',
            'content_type' => 'post',
            'dialect'      => 'fos',
            'action'       => 'draft',
        ]);

    $response->assertSessionHasErrors(['platform']);
});
