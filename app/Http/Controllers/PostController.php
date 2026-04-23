<?php

// SCH-07: posts history + SCH-08: reschedule / delete

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        // Aggregate stats (full workspace, not filtered)
        $aggregates = $workspace
            ? Post::withoutWorkspaceScope()
                ->where('workspace_id', $workspace->id)
                ->selectRaw('status, COUNT(*) as cnt')
                ->groupBy('status')
                ->get()
                ->mapWithKeys(fn ($r) => [$r->status => (int) ($r->getAttributes()['cnt'] ?? 0)])
            : collect();

        $thisMonth = $workspace
            ? Post::withoutWorkspaceScope()
                ->where('workspace_id', $workspace->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count()
            : 0;

        $query = $workspace
            ? Post::withoutWorkspaceScope()
                ->where('workspace_id', $workspace->id)
                ->with('socialAccount:id,provider,account_name')
            : Post::whereRaw('0=1');

        // Filters
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($platform = $request->query('platform')) {
            $query->where('platform', $platform);
        }
        if ($contentType = $request->query('content_type')) {
            $query->where('content_type', $contentType);
        }
        if ($search = $request->query('search')) {
            $query->where('content', 'like', "%{$search}%");
        }

        $posts = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return Inertia::render('Posts/Index', [
            'posts'   => $posts,
            'filters' => $request->only(['status', 'platform', 'content_type', 'search']),
            'stats'   => [
                'total'      => $aggregates->sum(),
                'published'  => $aggregates->get('published', 0),
                'scheduled'  => $aggregates->get('scheduled', 0),
                'drafts'     => $aggregates->get('draft', 0),
                'failed'     => $aggregates->get('failed', 0),
                'this_month' => $thisMonth,
            ],
        ]);
    }

    // SCH-08: reschedule a post
    public function reschedule(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $data = $request->validate([
            'scheduled_for' => ['required', 'date', 'after:now'],
        ]);

        $post->update([
            'status'        => 'scheduled',
            'scheduled_for' => $data['scheduled_for'],
        ]);

        return back()->with('flash', ['success' => 'تم إعادة جدولة المنشور.']);
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);
        $post->delete();

        return back()->with('flash', ['success' => 'تم حذف المنشور.']);
    }

    // Stats summary for the current workspace (used by History KPI strip)
    public function stats(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('current_workspace');

        if (! $workspace) {
            return response()->json(['total' => 0, 'published' => 0, 'scheduled' => 0, 'drafts' => 0]);
        }

        $rows = Post::withoutWorkspaceScope()
            ->where('workspace_id', $workspace->id)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->get();

        $counts = $rows->mapWithKeys(fn ($row) => [$row->status => (int) ($row->getAttributes()['cnt'] ?? 0)]);

        return response()->json([
            'total'     => $counts->sum(),
            'published' => $counts->get('published', 0),
            'scheduled' => $counts->get('scheduled', 0),
            'drafts'    => $counts->get('draft', 0),
            'failed'    => $counts->get('failed', 0),
        ]);
    }
}
