<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiGeneration;
use App\Models\Post;
use App\Models\SocialAccount;
use App\Models\TokenTransaction;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->input('period', '30');
        $from   = match ($period) {
            '1'   => now()->subDay(),
            '7'   => now()->subDays(7),
            '30'  => now()->subDays(30),
            '90'  => now()->subDays(90),
            default => null,
        };

        // ── All-time totals ────────────────────────────────────────────
        $totalUsers            = User::count();
        $newUsersToday         = User::whereDate('created_at', today())->count();
        $bannedUsers           = User::whereNotNull('banned_at')->count();
        $totalWorkspaces       = Workspace::count();
        $suspendedWorkspaces   = Workspace::whereNotNull('suspended_at')->count();
        $archivedWorkspaces    = Workspace::whereNotNull('archived_at')->count();
        $totalPosts            = Post::withoutWorkspaceScope()->count();
        $scheduledPosts        = Post::withoutWorkspaceScope()->where('status', 'scheduled')->count();
        $publishedPosts        = Post::withoutWorkspaceScope()->where('status', 'published')->count();
        $failedPosts           = Post::withoutWorkspaceScope()->where('status', 'failed')->count();
        $draftPosts            = Post::withoutWorkspaceScope()->where('status', 'draft')->count();
        $totalGenerations      = AiGeneration::withoutWorkspaceScope()->count();
        $generationsToday      = AiGeneration::withoutWorkspaceScope()->whereDate('created_at', today())->count();
        $totalTokensCharged    = AiGeneration::withoutWorkspaceScope()->sum('sada_tokens_charged');
        $totalInputTokens      = AiGeneration::withoutWorkspaceScope()->sum('input_tokens');
        $totalOutputTokens     = AiGeneration::withoutWorkspaceScope()->sum('output_tokens');
        $totalSocialAccounts   = SocialAccount::withoutWorkspaceScope()->count();
        $healthySocialAccounts = SocialAccount::withoutWorkspaceScope()->where('status', 'healthy')->count();
        $expiredSocialAccounts = SocialAccount::withoutWorkspaceScope()->where('status', 'expired')->count();
        $totalRevenue          = (int) TokenTransaction::where('type', 'purchase')->sum('amount');

        // ── Period-scoped stats ────────────────────────────────────────
        $newUsersInPeriod    = User::when($from, fn ($q) => $q->where('created_at', '>=', $from))->count();
        $generationsInPeriod = AiGeneration::withoutWorkspaceScope()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))->count();
        $tokensInPeriod      = (int) AiGeneration::withoutWorkspaceScope()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))->sum('sada_tokens_charged');
        $revenueInPeriod     = (int) TokenTransaction::where('type', 'purchase')
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))->sum('amount');

        // ── Charts (period-filtered) ───────────────────────────────────
        $userGrowth = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count'),
        )
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $monthExpr    = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";
        $revenueChart = TokenTransaction::select(
            DB::raw("{$monthExpr} as month"),
            DB::raw('SUM(amount) as total'),
        )
            ->where('type', 'purchase')
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $generationsChart = AiGeneration::withoutWorkspaceScope()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(sada_tokens_charged) as tokens'),
            )
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ── Recent items (not period-filtered) ────────────────────────
        $recentUsers = User::latest()
            ->limit(8)
            ->get(['id', 'name', 'email', 'created_at', 'banned_at', 'is_admin', 'token_balance']);

        $recentGenerations = AiGeneration::withoutWorkspaceScope()
            ->with(['workspace:id,name', 'user:id,name'])
            ->latest()
            ->limit(8)
            ->get(['id', 'workspace_id', 'user_id', 'agent_type', 'platform', 'sada_tokens_charged', 'cached', 'created_at']);

        $recentFailedPosts = Post::withoutWorkspaceScope()
            ->with('workspace:id,name')
            ->where('status', 'failed')
            ->latest()
            ->limit(5)
            ->get(['id', 'workspace_id', 'platform', 'status', 'scheduled_for', 'created_at']);

        return Inertia::render('Admin/Dashboard', compact(
            'totalUsers', 'newUsersToday', 'bannedUsers',
            'totalWorkspaces', 'suspendedWorkspaces', 'archivedWorkspaces',
            'totalPosts', 'scheduledPosts', 'publishedPosts', 'failedPosts', 'draftPosts',
            'totalGenerations', 'generationsToday', 'totalTokensCharged', 'totalInputTokens', 'totalOutputTokens',
            'totalSocialAccounts', 'healthySocialAccounts', 'expiredSocialAccounts',
            'totalRevenue',
            'newUsersInPeriod', 'generationsInPeriod', 'tokensInPeriod', 'revenueInPeriod',
            'userGrowth', 'revenueChart', 'generationsChart',
            'recentUsers', 'recentGenerations', 'recentFailedPosts',
            'period',
        ));
    }
}
