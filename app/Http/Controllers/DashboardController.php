<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user      = $request->user();
        $workspace = $request->attributes->get('current_workspace');

        $postStats = ['total' => 0, 'published' => 0, 'scheduled' => 0, 'draft' => 0, 'failed' => 0];
        $recentPosts    = [];
        $socialAccounts = [];

        if ($workspace) {
            $rows = Post::withoutWorkspaceScope()
                ->where('workspace_id', $workspace->id)
                ->selectRaw('status, COUNT(*) as cnt')
                ->groupBy('status')
                ->get()
                ->mapWithKeys(fn ($r) => [$r->status => (int) ($r->getAttributes()['cnt'] ?? 0)]);

            $postStats = [
                'total'     => $rows->sum(),
                'published' => $rows->get('published', 0),
                'scheduled' => $rows->get('scheduled', 0),
                'draft'     => $rows->get('draft', 0),
                'failed'    => $rows->get('failed', 0),
            ];

            $recentPosts = Post::withoutWorkspaceScope()
                ->where('workspace_id', $workspace->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'content', 'platform', 'status', 'created_at', 'scheduled_for'])
                ->map(fn ($p) => [
                    'id'            => $p->id,
                    'content'       => mb_strimwidth($p->content ?? '', 0, 80, '…'),
                    'platform'      => $p->platform,
                    'status'        => $p->status,
                    'created_at'    => $p->created_at?->toIso8601String(),
                    'scheduled_for' => $p->scheduled_for?->toIso8601String(),
                ]);

            $socialAccounts = SocialAccount::withoutWorkspaceScope()
                ->where('workspace_id', $workspace->id)
                ->get(['id', 'provider', 'account_name', 'status'])
                ->map(fn ($a) => [
                    'id'           => $a->id,
                    'provider'     => $a->provider,
                    'account_name' => $a->account_name,
                    'status'       => $a->status,
                ]);
        }

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'token_balance'   => $user->token_balance ?? 0,
                'posts'           => $postStats,
                'social_accounts' => count($socialAccounts),
                'workspaces'      => $user->workspaces()->count(),
            ],
            'recentPosts'    => $recentPosts,
            'socialAccounts' => $socialAccounts,
        ]);
    }
}
