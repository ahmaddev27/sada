<?php

// ADS-01→ADS-11 (admin view)

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminCampaignController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Campaign::with(['workspace:id,name', 'socialAccount:id,provider,account_name'])
            ->withoutWorkspaceScope()
            ->orderByDesc('created_at');

        if ($search = $request->string('search')->toString()) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->string('platform')->toString());
        }

        $campaigns = $query->paginate(25)->withQueryString();

        $stats = [
            'total'     => Campaign::withoutWorkspaceScope()->count(),
            'active'    => Campaign::withoutWorkspaceScope()->where('status', 'active')->count(),
            'pending'   => Campaign::withoutWorkspaceScope()->where('status', 'pending')->count(),
            'draft'     => Campaign::withoutWorkspaceScope()->where('status', 'draft')->count(),
            'paused'    => Campaign::withoutWorkspaceScope()->where('status', 'paused')->count(),
            'today'     => Campaign::withoutWorkspaceScope()->whereDate('created_at', today())->count(),
            'budget_total' => (float) Campaign::withoutWorkspaceScope()
                ->whereIn('status', ['active', 'pending', 'completed'])
                ->sum('budget_amount'),
        ];

        return Inertia::render('Admin/Campaigns/Index', [
            'campaigns' => $campaigns,
            'filters'   => $request->only('search', 'status', 'platform'),
            'stats'     => $stats,
        ]);
    }

    public function forceStatus(Campaign $campaign, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:draft,pending,active,paused,completed,rejected'],
        ]);

        $campaign->update(['status' => $data['status']]);

        return back()->with('success', 'تم تحديث حالة الحملة.');
    }
}
