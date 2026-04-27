<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Services\Admin\AdminLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminWorkspaceController extends Controller
{
    public function __construct(private readonly AdminLogService $log) {}

    public function index(Request $request): Response
    {
        $query = Workspace::with('user:id,name,email')
            ->withCount('posts')
            ->orderByDesc('created_at');

        if ($search = $request->string('search')->toString()) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            match ($request->string('status')->toString()) {
                'suspended' => $query->whereNotNull('suspended_at'),
                'active'    => $query->whereNull('suspended_at')->whereNull('archived_at'),
                'archived'  => $query->whereNotNull('archived_at'),
                default     => null,
            };
        }

        $workspaces = $query->paginate(25)->withQueryString();

        $stats = [
            'total'     => Workspace::count(),
            'active'    => Workspace::whereNull('suspended_at')->whereNull('archived_at')->count(),
            'suspended' => Workspace::whereNotNull('suspended_at')->count(),
            'archived'  => Workspace::whereNotNull('archived_at')->count(),
            'today'     => Workspace::whereDate('created_at', today())->count(),
        ];

        return Inertia::render('Admin/Workspaces/Index', [
            'workspaces' => $workspaces,
            'filters'    => $request->only('search', 'status'),
            'stats'      => $stats,
        ]);
    }

    public function suspend(Workspace $workspace, Request $request): RedirectResponse
    {
        $workspace->update(['suspended_at' => now()]);

        $this->log->log($request->user()->id, 'suspend_workspace', Workspace::class, $workspace->id);

        return back()->with('success', 'تم تعليق الـ workspace.');
    }

    public function restore(Workspace $workspace, Request $request): RedirectResponse
    {
        $workspace->update(['suspended_at' => null]);

        $this->log->log($request->user()->id, 'restore_workspace', Workspace::class, $workspace->id);

        return back()->with('success', 'تم استعادة الـ workspace.');
    }
}
