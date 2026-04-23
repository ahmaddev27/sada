<?php

// ADS-01: campaigns index page renders
// ADS-02: create campaign stores and redirects
// ADS-03: show campaign page renders
// ADS-04: update campaign
// ADS-05: only draft campaigns can be deleted
// ADS-09: pause / resume / duplicate

use App\Jobs\SubmitCampaignToMetaJob;
use App\Models\Campaign;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Queue;

function makeCampaignWorld(): array
{
    $user      = User::factory()->create(['token_balance' => 200]);
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $account   = SocialAccount::factory()->instagram()->for($workspace)->create();

    return [$user, $workspace, $account];
}

function campaignPayload(int $accountId): array
{
    return [
        'name'              => 'حملة اختبار الرياض',
        'objective'         => 'awareness',
        'platform'          => 'instagram',
        'social_account_id' => $accountId,
        'ad_copy'           => 'محتوى الإعلان التجريبي',
        'target_countries'  => ['sa', 'ae'],
        'target_age_min'    => 18,
        'target_age_max'    => 45,
        'target_gender'     => 'all',
        'target_interests'  => [],
        'budget_type'       => 'daily',
        'budget_amount'     => 100,
        'budget_currency'   => 'SAR',
        'starts_at'         => now()->addDay()->toDateString(),
        'ends_at'           => now()->addDays(10)->toDateString(),
        'status'            => 'draft',
    ];
}

// ── ADS-01: Index ────────────────────────────────────────────────────────────

it('تعرض صفحة الحملات للمستخدم المُسجَّل', function () {
    [$user, $workspace] = makeCampaignWorld();
    session(['current_workspace_id' => $workspace->id]);

    $this->actingAs($user)
        ->get(route('campaigns.index'))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Campaigns/Index'));
});

it('يُصفّي الحملات حسب الحالة', function () {
    [$user, $workspace, $account] = makeCampaignWorld();
    session(['current_workspace_id' => $workspace->id]);

    Campaign::withoutWorkspaceScope()->create(array_merge(
        campaignPayload($account->id),
        ['workspace_id' => $workspace->id, 'status' => 'active']
    ));
    Campaign::withoutWorkspaceScope()->create(array_merge(
        campaignPayload($account->id),
        ['workspace_id' => $workspace->id, 'name' => 'حملة مسودة', 'status' => 'draft']
    ));

    $this->actingAs($user)
        ->get(route('campaigns.index', ['status' => 'active']))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('campaigns.total', 1));
});

// ── ADS-02: Store ────────────────────────────────────────────────────────────

it('ينشئ حملة مسودة بنجاح', function () {
    [$user, $workspace, $account] = makeCampaignWorld();
    session(['current_workspace_id' => $workspace->id]);

    $this->actingAs($user)
        ->post(route('campaigns.store'), campaignPayload($account->id))
        ->assertRedirect();

    $this->assertDatabaseHas('campaigns', [
        'workspace_id' => $workspace->id,
        'name'         => 'حملة اختبار الرياض',
        'status'       => 'draft',
    ]);
});

it('يرفض إنشاء حملة بدون اسم', function () {
    [$user, $workspace, $account] = makeCampaignWorld();
    session(['current_workspace_id' => $workspace->id]);

    $payload = campaignPayload($account->id);
    unset($payload['name']);

    $this->actingAs($user)
        ->post(route('campaigns.store'), $payload)
        ->assertSessionHasErrors('name');
});

it('يرفض تاريخ انتهاء قبل تاريخ البدء', function () {
    [$user, $workspace, $account] = makeCampaignWorld();
    session(['current_workspace_id' => $workspace->id]);

    $payload             = campaignPayload($account->id);
    $payload['ends_at']  = now()->subDay()->toDateString();

    $this->actingAs($user)
        ->post(route('campaigns.store'), $payload)
        ->assertSessionHasErrors('ends_at');
});

// ── ADS-03: Show ─────────────────────────────────────────────────────────────

it('تعرض صفحة تفاصيل الحملة', function () {
    [$user, $workspace, $account] = makeCampaignWorld();
    session(['current_workspace_id' => $workspace->id]);

    $campaign = Campaign::withoutWorkspaceScope()->create(array_merge(
        campaignPayload($account->id),
        ['workspace_id' => $workspace->id]
    ));

    $this->actingAs($user)
        ->get(route('campaigns.show', $campaign))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Campaigns/Show'));
});

it('يمنع المستخدم من مشاهدة حملة مساحة عمل أخرى', function () {
    [$user, $ws1]        = makeCampaignWorld();
    [, $ws2, $acct2] = makeCampaignWorld();
    session(['current_workspace_id' => $ws1->id]);

    $campaign = Campaign::withoutWorkspaceScope()->create(array_merge(
        campaignPayload($acct2->id),
        ['workspace_id' => $ws2->id]
    ));

    // Global scope filters by current workspace → model not found → 404
    $this->actingAs($user)
        ->get(route('campaigns.show', $campaign))
        ->assertNotFound();
});

// ── ADS-05: Destroy ──────────────────────────────────────────────────────────

it('يحذف الحملة المسودة', function () {
    [$user, $workspace, $account] = makeCampaignWorld();
    session(['current_workspace_id' => $workspace->id]);

    $campaign = Campaign::withoutWorkspaceScope()->create(array_merge(
        campaignPayload($account->id),
        ['workspace_id' => $workspace->id, 'status' => 'draft']
    ));

    $this->actingAs($user)
        ->delete(route('campaigns.destroy', $campaign))
        ->assertRedirect(route('campaigns.index'));

    $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
});

it('يمنع حذف الحملة النشطة', function () {
    [$user, $workspace, $account] = makeCampaignWorld();
    session(['current_workspace_id' => $workspace->id]);

    $campaign = Campaign::withoutWorkspaceScope()->create(array_merge(
        campaignPayload($account->id),
        ['workspace_id' => $workspace->id, 'status' => 'active']
    ));

    $this->actingAs($user)
        ->delete(route('campaigns.destroy', $campaign))
        ->assertForbidden();

    $this->assertDatabaseHas('campaigns', ['id' => $campaign->id]);
});

// ── ADS-09: Pause / Resume ───────────────────────────────────────────────────

it('يوقف الحملة النشطة', function () {
    [$user, $workspace, $account] = makeCampaignWorld();
    session(['current_workspace_id' => $workspace->id]);

    $campaign = Campaign::withoutWorkspaceScope()->create(array_merge(
        campaignPayload($account->id),
        ['workspace_id' => $workspace->id, 'status' => 'active']
    ));

    $this->actingAs($user)
        ->post(route('campaigns.pause', $campaign))
        ->assertRedirect();

    expect($campaign->fresh()->status)->toBe('paused');
});

it('يرفض إيقاف حملة غير نشطة', function () {
    [$user, $workspace, $account] = makeCampaignWorld();
    session(['current_workspace_id' => $workspace->id]);

    $campaign = Campaign::withoutWorkspaceScope()->create(array_merge(
        campaignPayload($account->id),
        ['workspace_id' => $workspace->id, 'status' => 'draft']
    ));

    $this->actingAs($user)
        ->post(route('campaigns.pause', $campaign))
        ->assertStatus(422);
});

it('يستأنف الحملة الموقوفة ويضعها في قائمة الانتظار', function () {
    Queue::fake();

    [$user, $workspace, $account] = makeCampaignWorld();
    session(['current_workspace_id' => $workspace->id]);

    $campaign = Campaign::withoutWorkspaceScope()->create(array_merge(
        campaignPayload($account->id),
        ['workspace_id' => $workspace->id, 'status' => 'paused']
    ));

    $this->actingAs($user)
        ->post(route('campaigns.resume', $campaign))
        ->assertRedirect();

    expect($campaign->fresh()->status)->toBe('pending');
    Queue::assertPushed(SubmitCampaignToMetaJob::class);
});

// ── ADS-09: Duplicate ────────────────────────────────────────────────────────

it('ينسخ الحملة كمسودة جديدة', function () {
    [$user, $workspace, $account] = makeCampaignWorld();
    session(['current_workspace_id' => $workspace->id]);

    $campaign = Campaign::withoutWorkspaceScope()->create(array_merge(
        campaignPayload($account->id),
        ['workspace_id' => $workspace->id, 'status' => 'active', 'meta_campaign_id' => 'meta-123']
    ));

    $this->actingAs($user)
        ->post(route('campaigns.duplicate', $campaign))
        ->assertRedirect();

    $duplicate = Campaign::withoutWorkspaceScope()
        ->where('name', 'حملة اختبار الرياض — نسخة')
        ->first();

    expect($duplicate)->not->toBeNull();
    expect($duplicate->status)->toBe('draft');
    expect($duplicate->meta_campaign_id)->toBeNull();
});
