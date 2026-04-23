<?php

namespace App\Http\Controllers;

use App\Actions\Workspace\CreateWorkspaceAction;
use App\Actions\Workspace\SwitchWorkspaceAction;
use App\Actions\Workspace\UpdateBrandIdentityAction;
use App\Actions\Workspace\UpdateWorkspaceAction;
use App\Http\Requests\Workspace\CreateWorkspaceRequest;
use App\Http\Requests\Workspace\UpdateBrandIdentityRequest;
use App\Http\Requests\Workspace\UpdateWorkspaceRequest;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController extends Controller
{
    // WS-01: list + create form
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user       = $request->user();
        $workspaces = $user->activeWorkspaces()
            ->with('brandIdentity')
            ->latest()
            ->get();

        /** @var Workspace|null $current */
        $current = $request->attributes->get('current_workspace');

        return Inertia::render('Workspace/Index', [
            'workspaces'         => $workspaces,
            'currentWorkspaceId' => $current?->id,
        ]);
    }

    // WS-01: create
    public function store(CreateWorkspaceRequest $request, CreateWorkspaceAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $workspace = $action->execute(
            user:           $user,
            name:           $request->string('name')->trim()->toString(),
            businessType:   $request->string('business_type')->toString() ?: null,
            countries:      (array) $request->input('countries', []),
            defaultDialect: $request->string('default_dialect')->toString() ?: 'sa',
        );

        $request->session()->put('current_workspace_id', $workspace->id);

        return redirect()->route('workspace.settings', $workspace)
            ->with('flash.success', 'تم إنشاء مساحة العمل بنجاح.');
    }

    // WS-03: settings page
    public function settings(Workspace $workspace): Response
    {
        $this->authorizeWorkspace($workspace);

        return Inertia::render('Workspace/Settings', [
            'workspace'     => $workspace->load('brandIdentity'),
            'brandIdentity' => $workspace->brandIdentity,
        ]);
    }

    // WS-03: update workspace info
    public function update(
        UpdateWorkspaceRequest $request,
        Workspace $workspace,
        UpdateWorkspaceAction $action,
    ): RedirectResponse {
        $this->authorizeWorkspace($workspace);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            // delete old logo
            if ($workspace->logo_path) {
                Storage::disk('public')->delete($workspace->logo_path);
            }
            $logoPath = $request->file('logo')->store('logos', 'public') ?: null;
        }

        $action->execute(
            workspace:      $workspace,
            name:           $request->string('name')->trim()->toString(),
            businessType:   $request->string('business_type')->toString() ?: null,
            countries:      (array) $request->input('countries', []),
            defaultDialect: $request->string('default_dialect')->toString() ?: 'sa',
            logoPath:       $logoPath,
        );

        return back()->with('flash.success', 'تم تحديث مساحة العمل.');
    }

    // BI-01: update brand identity
    public function updateBrand(
        UpdateBrandIdentityRequest $request,
        Workspace $workspace,
        UpdateBrandIdentityAction $action,
    ): RedirectResponse {
        $this->authorizeWorkspace($workspace);

        $action->execute(
            workspace:      $workspace,
            description:    $request->string('description')->toString() ?: null,
            tone:           $request->string('tone')->toString() ?: null,
            targetAudience: $request->string('target_audience')->toString() ?: null,
            bannedWords:    (array) $request->input('banned_words', []),
            examplePosts:   (array) $request->input('example_posts', []),
        );

        return back()->with('flash.success', 'تم حفظ هوية العلامة التجارية.');
    }

    // WS-02: switch active workspace
    public function switch(Request $request, Workspace $workspace, SwitchWorkspaceAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $action->execute($request, $user, $workspace->id);

        return back()->with('flash.success', 'تم التبديل إلى ' . $workspace->name);
    }

    // WS-05: archive workspace
    public function archive(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspace($workspace);

        $workspace->archive();

        /** @var \App\Models\User $user */
        $user = $request->user();

        // switch to another workspace if current was archived
        if ($request->session()->get('current_workspace_id') === $workspace->id) {
            /** @var Workspace|null $next */
            $next = $user->activeWorkspaces()->first();
            $request->session()->put('current_workspace_id', $next?->id);
        }

        return redirect()->route('workspace.index')
            ->with('flash.success', 'تم أرشفة مساحة العمل.');
    }

    private function authorizeWorkspace(Workspace $workspace): void
    {
        abort_unless(
            auth()->id() === $workspace->user_id,
            403,
            'غير مصرح لك بالوصول إلى هذه المساحة.',
        );
    }
}
