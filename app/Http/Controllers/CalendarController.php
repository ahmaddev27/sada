<?php

// SCH-07: content calendar view

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function index(Request $request): Response
    {
        $workspace = $request->attributes->get('current_workspace');

        $year  = (int) ($request->query('year',  now()->year));
        $month = (int) ($request->query('month', now()->month));

        $posts = $workspace
            ? Post::withoutWorkspaceScope()
                ->where('workspace_id', $workspace->id)
                ->whereYear('scheduled_for', $year)
                ->whereMonth('scheduled_for', $month)
                ->whereIn('status', ['scheduled', 'published', 'failed'])
                ->orderBy('scheduled_for')
                ->get(['id', 'content', 'platform', 'content_type', 'status', 'scheduled_for', 'hashtags'])
            : collect();

        // Also include drafts with no scheduled_for in a separate bucket
        $drafts = $workspace
            ? Post::withoutWorkspaceScope()
                ->where('workspace_id', $workspace->id)
                ->where('status', 'draft')
                ->whereNull('scheduled_for')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get(['id', 'content', 'platform', 'content_type', 'status', 'created_at'])
            : collect();

        return Inertia::render('Calendar/Index', [
            'posts'  => $posts,
            'drafts' => $drafts,
            'year'   => $year,
            'month'  => $month,
        ]);
    }
}
