<?php

// ADS-01→ADS-11

namespace App\Http\Controllers;

use App\Actions\Campaigns\CreateCampaignAction;
use App\Actions\Campaigns\UpdateCampaignAction;
use App\Http\Requests\Campaign\StoreCampaignRequest;
use App\Http\Requests\Campaign\UpdateCampaignRequest;
use App\Jobs\SubmitCampaignToMetaJob;
use App\Models\Campaign;
use App\Models\Post;
use App\Models\SocialAccount;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    // ADS-01: create campaign page
    public function create(Request $request): Response
    {
        /** @var Workspace $workspace */
        $workspace = $request->attributes->get('current_workspace');

        $socialAccounts = SocialAccount::withoutWorkspaceScope()
            ->where('workspace_id', $workspace->id)
            ->whereIn('provider', ['instagram', 'facebook'])
            ->get(['id', 'provider', 'account_name']);

        $posts = Post::withoutWorkspaceScope()
            ->where('workspace_id', $workspace->id)
            ->where('status', 'published')
            ->latest()
            ->get(['id', 'content', 'platform', 'published_at']);

        return Inertia::render('Campaigns/Create', [
            'socialAccounts' => $socialAccounts,
            'posts'          => $posts,
        ]);
    }

    // ADS-01: list campaigns with optional status filter
    public function index(Request $request): Response
    {
        /** @var Workspace $workspace */
        $workspace = $request->attributes->get('current_workspace');

        $campaigns = Campaign::with(['socialAccount', 'post'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Social accounts for the create form (Meta platforms only)
        $socialAccounts = SocialAccount::withoutWorkspaceScope()
            ->where('workspace_id', $workspace->id)
            ->whereIn('provider', ['instagram', 'facebook'])
            ->get(['id', 'provider', 'account_name', 'status']);

        // Published posts as ad creatives picker
        $posts = Post::withoutWorkspaceScope()
            ->where('workspace_id', $workspace->id)
            ->where('status', 'published')
            ->latest()
            ->get(['id', 'content', 'platform', 'published_at']);

        return Inertia::render('Campaigns/Index', [
            'campaigns'      => $campaigns,
            'filters'        => ['status' => $request->query('status')],
            'socialAccounts' => $socialAccounts,
            'posts'          => $posts,
        ]);
    }

    // ADS-02: create campaign
    public function store(StoreCampaignRequest $request, CreateCampaignAction $action): RedirectResponse
    {
        /** @var Workspace $workspace */
        $workspace = $request->attributes->get('current_workspace');

        try {
            $campaign = $action->execute($workspace, $request->validated());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['social_account_id' => $e->getMessage()]);
        }

        return redirect()->route('campaigns.show', $campaign)
            ->with('flash.success', 'تم إنشاء الحملة بنجاح.');
    }

    // ADS-03: show campaign details
    public function show(Campaign $campaign): Response
    {
        $this->authorizeCampaign($campaign);

        $campaign->load(['socialAccount', 'post', 'workspace']);

        return Inertia::render('Campaigns/Show', [
            'campaign' => $campaign,
        ]);
    }

    // ADS-04: update campaign
    public function update(
        UpdateCampaignRequest $request,
        Campaign $campaign,
        UpdateCampaignAction $action,
    ): RedirectResponse {
        $this->authorizeCampaign($campaign);

        $campaign = $action->execute($campaign, $request->validated());

        return back()->with('flash.success', 'تم تحديث الحملة.');
    }

    // ADS-05: delete draft campaign only
    public function destroy(Campaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($campaign);

        abort_unless(
            $campaign->isDraft(),
            403,
            'لا يمكن حذف إلا الحملات في حالة المسودة.',
        );

        $campaign->delete();

        return redirect()->route('campaigns.index')
            ->with('flash.success', 'تم حذف الحملة.');
    }

    // ADS-09: pause active campaign
    public function pause(Campaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($campaign);

        abort_unless(
            $campaign->isActive(),
            422,
            'لا يمكن إيقاف إلا الحملات النشطة.',
        );

        $campaign->update(['status' => 'paused']);

        return back()->with('flash.success', 'تم إيقاف الحملة مؤقتاً.');
    }

    // ADS-09: resume paused campaign
    public function resume(Campaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($campaign);

        abort_unless(
            $campaign->isPaused(),
            422,
            'لا يمكن استئناف إلا الحملات الموقوفة.',
        );

        $campaign->update(['status' => 'pending']);

        SubmitCampaignToMetaJob::dispatch($campaign);

        return back()->with('flash.success', 'تم إعادة تشغيل الحملة.');
    }

    // ADS-09: duplicate campaign as a new draft
    public function duplicate(Campaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($campaign);

        $duplicate = $campaign->replicate(['meta_campaign_id', 'meta_adset_id', 'meta_ad_id', 'insights', 'insights_synced_at']);
        $duplicate->name   = $campaign->name . ' — نسخة';
        $duplicate->status = 'draft';
        $duplicate->save();

        return redirect()->route('campaigns.show', $duplicate)
            ->with('flash.success', 'تم نسخ الحملة بنجاح.');
    }

    // ── Authorization ──────────────────────────────────────────────────────────

    private function authorizeCampaign(Campaign $campaign): void
    {
        $workspaceId = session('current_workspace_id');

        abort_unless(
            $campaign->workspace_id === $workspaceId,
            403,
            'غير مصرح لك بالوصول إلى هذه الحملة.',
        );
    }
}
