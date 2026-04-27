<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminPostsController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Post::with(['workspace:id,name', 'user:id,name', 'socialAccount:id,provider'])
            ->orderByDesc('created_at');

        if ($search = $request->string('search')->toString()) {
            $query->where('content', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->string('platform')->toString());
        }

        $posts = $query->paginate(30)->withQueryString();

        $counts = [
            'all'       => Post::count(),
            'scheduled' => Post::where('status', 'scheduled')->count(),
            'published' => Post::where('status', 'published')->count(),
            'failed'    => Post::where('status', 'failed')->count(),
            'draft'     => Post::where('status', 'draft')->count(),
        ];

        return Inertia::render('Admin/Posts/Index', [
            'posts'   => $posts,
            'counts'  => $counts,
            'filters' => $request->only('search', 'status', 'platform'),
        ]);
    }
}
