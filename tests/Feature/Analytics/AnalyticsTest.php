<?php

// ANL-01: analytics page renders with KPIs
// ANL-02: engagement over time + top posts + platform breakdown
// ANL-04: filter by platform and date range
// ANL-05: PDF export returns a PDF response
// ANL-06: CSV export returns a CSV response

use App\Models\AnalyticsSnapshot;
use App\Models\Post;
use App\Models\User;
use App\Models\Workspace;

function makeAnalyticsWorld(): array
{
    $user      = User::factory()->create(['token_balance' => 200]);
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    return [$user, $workspace];
}

function makeSnapshot(Workspace $workspace, array $overrides = []): AnalyticsSnapshot
{
    return AnalyticsSnapshot::withoutWorkspaceScope()->create(array_merge([
        'workspace_id'  => $workspace->id,
        'platform'      => 'instagram',
        'snapshot_date' => now()->subDays(3)->toDateString(),
        'reach'         => 1000,
        'impressions'   => 5000,
        'likes'         => 200,
        'comments'      => 50,
        'shares'        => 30,
        'saves'         => 20,
        'clicks'        => 100,
        'spend'         => 50.00,
        'follower_count' => 5000,
    ], $overrides));
}

// ── ANL-01: Index renders ────────────────────────────────────────────────────

it('تعرض صفحة التحليلات للمستخدم المُسجَّل', function () {
    [$user, $workspace] = makeAnalyticsWorld();
    session(['current_workspace_id' => $workspace->id]);

    $this->actingAs($user)
        ->get(route('analytics.index'))
        ->assertOk()
        ->assertInertia(fn ($p) => $p
            ->component('Analytics/Index')
            ->has('kpis')
            ->has('engagementOverTime')
            ->has('topPosts')
            ->has('platformBreakdown')
            ->has('aiInsights')
            ->has('filters')
        );
});

it('تُظهر صفحة التحليلات hasData=false عند غياب البيانات', function () {
    [$user, $workspace] = makeAnalyticsWorld();
    session(['current_workspace_id' => $workspace->id]);

    $this->actingAs($user)
        ->get(route('analytics.index'))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('hasData', false));
});

it('تُظهر صفحة التحليلات hasData=true عند وجود بيانات', function () {
    [$user, $workspace] = makeAnalyticsWorld();
    session(['current_workspace_id' => $workspace->id]);

    makeSnapshot($workspace);

    $this->actingAs($user)
        ->get(route('analytics.index'))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('hasData', true));
});

// ── ANL-01: KPI aggregation ──────────────────────────────────────────────────

it('يحسب إجمالي الوصول والتفاعل بشكل صحيح', function () {
    [$user, $workspace] = makeAnalyticsWorld();
    session(['current_workspace_id' => $workspace->id]);

    makeSnapshot($workspace, ['reach' => 1000, 'likes' => 100, 'comments' => 50, 'shares' => 20, 'saves' => 10]);
    makeSnapshot($workspace, ['reach' => 2000, 'likes' => 200, 'comments' => 80, 'shares' => 40, 'saves' => 20]);

    $this->actingAs($user)
        ->get(route('analytics.index'))
        ->assertOk()
        ->assertInertia(fn ($p) => $p
            ->where('kpis.total_reach', 3000)
            ->where('kpis.total_engagement', 520)
        );
});

it('يحسب نمو المتابعين بين أول وآخر لقطة', function () {
    [$user, $workspace] = makeAnalyticsWorld();
    session(['current_workspace_id' => $workspace->id]);

    makeSnapshot($workspace, [
        'snapshot_date'  => now()->subDays(5)->toDateString(),
        'follower_count' => 4000,
    ]);
    makeSnapshot($workspace, [
        'snapshot_date'  => now()->subDay()->toDateString(),
        'follower_count' => 4500,
    ]);

    $this->actingAs($user)
        ->get(route('analytics.index'))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('kpis.follower_growth', 500));
});

// ── ANL-04: Platform filter ──────────────────────────────────────────────────

it('يُصفّي التحليلات حسب المنصة', function () {
    [$user, $workspace] = makeAnalyticsWorld();
    session(['current_workspace_id' => $workspace->id]);

    makeSnapshot($workspace, ['platform' => 'instagram', 'reach' => 800]);
    makeSnapshot($workspace, ['platform' => 'facebook',  'reach' => 300]);

    $this->actingAs($user)
        ->get(route('analytics.index', ['platform' => 'instagram']))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('kpis.total_reach', 800));
});

it('يتجاهل قيمة platform غير الصحيحة ويُرجع كل المنصات', function () {
    [$user, $workspace] = makeAnalyticsWorld();
    session(['current_workspace_id' => $workspace->id]);

    makeSnapshot($workspace, ['platform' => 'instagram', 'reach' => 600]);
    makeSnapshot($workspace, ['platform' => 'facebook',  'reach' => 400]);

    $this->actingAs($user)
        ->get(route('analytics.index', ['platform' => 'منصة-غير-موجودة']))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('kpis.total_reach', 1000));
});

// ── ANL-04: Date range filter ────────────────────────────────────────────────

it('يُصفّي التحليلات بنطاق تاريخ محدد', function () {
    [$user, $workspace] = makeAnalyticsWorld();
    session(['current_workspace_id' => $workspace->id]);

    makeSnapshot($workspace, ['snapshot_date' => now()->subDays(2)->toDateString(), 'reach' => 500]);
    makeSnapshot($workspace, ['snapshot_date' => now()->subDays(60)->toDateString(), 'reach' => 999]);

    $this->actingAs($user)
        ->get(route('analytics.index', [
            'date_from' => now()->subDays(7)->toDateString(),
            'date_to'   => now()->toDateString(),
        ]))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('kpis.total_reach', 500));
});

// ── ANL-05: PDF export ───────────────────────────────────────────────────────

it('يُنزّل التقرير بصيغة PDF', function () {
    [$user, $workspace] = makeAnalyticsWorld();
    session(['current_workspace_id' => $workspace->id]);

    $response = $this->actingAs($user)
        ->get(route('analytics.export.pdf'));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/pdf');
});

// ── ANL-06: CSV export ───────────────────────────────────────────────────────

it('يُنزّل البيانات بصيغة CSV', function () {
    [$user, $workspace] = makeAnalyticsWorld();
    session(['current_workspace_id' => $workspace->id]);

    makeSnapshot($workspace);

    $response = $this->actingAs($user)
        ->get(route('analytics.export.csv'));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/csv');
});

it('يحتوي CSV على ترويسات ثنائية اللغة', function () {
    [$user, $workspace] = makeAnalyticsWorld();
    session(['current_workspace_id' => $workspace->id]);

    makeSnapshot($workspace);

    $response = $this->actingAs($user)
        ->get(route('analytics.export.csv'));

    $content = $response->streamedContent();

    expect($content)->toContain('التاريخ / Date');
    expect($content)->toContain('الوصول / Reach');
});
