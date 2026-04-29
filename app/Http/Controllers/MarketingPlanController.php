<?php

// MKT-01: Marketing Plan Generator

namespace App\Http\Controllers;

use App\Actions\MarketingPlan\GenerateMarketingPlanAction;
use App\Models\MarketingPlan;
use App\Models\SeasonalOccasion;
use App\Models\Workspace;
use App\Services\FeatureFlagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarketingPlanController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        if (! app(FeatureFlagService::class)->isEnabled('ai_generation')) {
            return redirect()->route('dashboard')
                ->with('flash', ['error' => 'ميزة توليد المحتوى بالذكاء الاصطناعي معطّلة مؤقتاً.']);
        }

        /** @var Workspace|null $workspace */
        $workspace = $request->attributes->get('current_workspace');

        if (! $workspace) {
            return redirect()->route('onboarding');
        }

        $plans = MarketingPlan::withoutWorkspaceScope()
            ->where('workspace_id', $workspace->id)
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'id'         => $p->id,
                'title'      => $p->title,
                'status'     => $p->status,
                'goal'       => $p->inputs['goal'] ?? null,
                'duration'   => $p->inputs['duration'] ?? null,
                'platforms'  => $p->inputs['platforms'] ?? [],
                'cost_usd'   => $p->cost_usd,
                'created_at' => $p->created_at->toIso8601String(),
            ]);

        return Inertia::render('MarketingPlan/Index', [
            'plans' => $plans,
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        /** @var Workspace|null $workspace */
        $workspace = $request->attributes->get('current_workspace');

        if (! $workspace) {
            return redirect()->route('onboarding');
        }

        $occasions = SeasonalOccasion::active()
            ->orderBy('sort_order')
            ->orderBy('date')
            ->get(['id', 'key', 'name', 'icon', 'color', 'type', 'date']);

        return Inertia::render('MarketingPlan/Create', [
            'workspace' => [
                'id'            => $workspace->id,
                'name'          => $workspace->name,
                'business_type' => $workspace->business_type,
            ],
            'occasions' => $occasions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Workspace|null $workspace */
        $workspace = $request->attributes->get('current_workspace');

        if (! $workspace) {
            return redirect()->route('onboarding');
        }

        $inputs = $request->validate([
            'business_name' => 'required|string|max:100',
            'business_type' => 'required|string|max:100',
            'description'   => 'required|string|max:500',
            'usp'           => 'required|string|max:300',
            'goal'          => 'required|in:awareness,sales,engagement,leads,retention',
            'duration'      => 'required|in:1_month,3_months,6_months,12_months',
            'budget'        => 'required|numeric|min:0',
            'currency'      => 'required|in:SAR,AED,KWD,QAR,BHD,OMR,USD',
            'countries'     => 'required|array|min:1',
            'countries.*'   => 'in:sa,ae,kw,qa,bh,om',
            'age_min'       => 'required|integer|min:13|max:65',
            'age_max'       => 'required|integer|min:13|max:65|gte:age_min',
            'gender'        => 'required|in:all,male,female',
            'interests'     => 'nullable|array',
            'interests.*'   => 'string|max:80',
            'platforms'     => 'required|array|min:1',
            'platforms.*'   => 'in:instagram,facebook,tiktok,snapchat,x',
            'occasions'     => 'nullable|array',
            'occasions.*'   => 'string|max:100',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $plan = app(GenerateMarketingPlanAction::class)->execute($inputs, $workspace, $user);

        if ($plan->status === 'failed') {
            return back()->with('flash', ['error' => 'فشل في توليد الخطة، يرجى المحاولة مرة أخرى.']);
        }

        return redirect()
            ->route('marketing-plan.show', $plan->id)
            ->with('flash', ['success' => 'تم توليد خطتك التسويقية بنجاح!']);
    }

    public function show(Request $request, MarketingPlan $marketingPlan): Response|RedirectResponse
    {
        /** @var Workspace|null $workspace */
        $workspace = $request->attributes->get('current_workspace');

        if (! $workspace || $marketingPlan->workspace_id !== $workspace->id) {
            abort(403);
        }

        return Inertia::render('MarketingPlan/Show', [
            'plan' => [
                'id'            => $marketingPlan->id,
                'title'         => $marketingPlan->title,
                'status'        => $marketingPlan->status,
                'inputs'        => $marketingPlan->inputs,
                'plan'          => $marketingPlan->plan,
                'ai_provider'   => $marketingPlan->ai_provider,
                'ai_model'      => $marketingPlan->ai_model,
                'cost_usd'      => $marketingPlan->cost_usd,
                'created_at'    => $marketingPlan->created_at->toIso8601String(),
            ],
        ]);
    }

    public function destroy(Request $request, MarketingPlan $marketingPlan): RedirectResponse
    {
        /** @var Workspace|null $workspace */
        $workspace = $request->attributes->get('current_workspace');

        if (! $workspace || $marketingPlan->workspace_id !== $workspace->id) {
            abort(403);
        }

        $marketingPlan->delete();

        return redirect()
            ->route('marketing-plan.index')
            ->with('flash', ['success' => 'تم حذف الخطة التسويقية.']);
    }
}
