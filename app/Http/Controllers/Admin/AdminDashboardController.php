<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiGeneration;
use App\Models\Post;
use App\Models\SocialAccount;
use App\Models\TokenTransaction;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        // Users
        $totalUsers    = User::count();
        $newUsersToday = User::whereDate('created_at', today())->count();
        $newUsersWeek  = User::where('created_at', '>=', now()->subDays(7))->count();
        $bannedUsers   = User::whereNotNull('banned_at')->count();

        // Workspaces
        $totalWorkspaces     = Workspace::count();
        $suspendedWorkspaces = Workspace::whereNotNull('suspended_at')->count();
        $archivedWorkspaces  = Workspace::whereNotNull('archived_at')->count();

        // Posts
        $totalPosts     = Post::withoutWorkspaceScope()->count();
        $scheduledPosts = Post::withoutWorkspaceScope()->where('status', 'scheduled')->count();
        $publishedPosts = Post::withoutWorkspaceScope()->where('status', 'published')->count();
        $failedPosts    = Post::withoutWorkspaceScope()->where('status', 'failed')->count();
        $draftPosts     = Post::withoutWorkspaceScope()->where('status', 'draft')->count();

        // AI Generations
        $totalGenerations   = AiGeneration::withoutWorkspaceScope()->count();
        $generationsToday   = AiGeneration::withoutWorkspaceScope()->whereDate('created_at', today())->count();
        $totalTokensCharged = AiGeneration::withoutWorkspaceScope()->sum('sada_tokens_charged');
        $totalInputTokens   = AiGeneration::withoutWorkspaceScope()->sum('input_tokens');
        $totalOutputTokens  = AiGeneration::withoutWorkspaceScope()->sum('output_tokens');

        // Social accounts
        $totalSocialAccounts   = SocialAccount::withoutWorkspaceScope()->count();
        $healthySocialAccounts = SocialAccount::withoutWorkspaceScope()->where('status', 'healthy')->count();
        $expiredSocialAccounts = SocialAccount::withoutWorkspaceScope()->where('status', 'expired')->count();

        // Revenue (token purchases)
        $totalRevenue = (int) TokenTransaction::where('type', 'purchase')->sum('amount');

        // User growth — last 30 days
        $userGrowth = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count'),
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Monthly revenue — last 6 months
        $monthExpr   = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";
        $revenueChart = TokenTransaction::select(
            DB::raw("{$monthExpr} as month"),
            DB::raw('SUM(amount) as total'),
        )
            ->where('type', 'purchase')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // AI generations — last 14 days
        $generationsChart = AiGeneration::withoutWorkspaceScope()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(sada_tokens_charged) as tokens'),
            )
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recent signups
        $recentUsers = User::latest()
            ->limit(8)
            ->get(['id', 'name', 'email', 'created_at', 'banned_at', 'is_admin', 'token_balance']);

        // Recent AI generations
        $recentGenerations = AiGeneration::withoutWorkspaceScope()
            ->with([
                'workspace:id,name',
                'user:id,name',
            ])
            ->latest()
            ->limit(8)
            ->get(['id', 'workspace_id', 'user_id', 'agent_type', 'platform', 'sada_tokens_charged', 'cached', 'created_at']);

        // Failed posts
        $recentFailedPosts = Post::withoutWorkspaceScope()
            ->with('workspace:id,name')
            ->where('status', 'failed')
            ->latest()
            ->limit(5)
            ->get(['id', 'workspace_id', 'platform', 'status', 'scheduled_for', 'created_at']);

        return Inertia::render('Admin/Dashboard', compact(
            'totalUsers', 'newUsersToday', 'newUsersWeek', 'bannedUsers',
            'totalWorkspaces', 'suspendedWorkspaces', 'archivedWorkspaces',
            'totalPosts', 'scheduledPosts', 'publishedPosts', 'failedPosts', 'draftPosts',
            'totalGenerations', 'generationsToday', 'totalTokensCharged', 'totalInputTokens', 'totalOutputTokens',
            'totalSocialAccounts', 'healthySocialAccounts', 'expiredSocialAccounts',
            'totalRevenue',
            'userGrowth', 'revenueChart', 'generationsChart',
            'recentUsers', 'recentGenerations', 'recentFailedPosts',
        ));
    }
}
