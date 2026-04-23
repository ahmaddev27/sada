<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\Model;

// WS-02: resolve current workspace from session, default to first active
class SetCurrentWorkspace
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $workspaceId = $request->session()->get('current_workspace_id');

        $user = $request->user();

        /** @var Workspace|null $workspace */
        $workspace = $workspaceId
            ? Workspace::query()->where('user_id', $user->id)->whereNull('archived_at')->find($workspaceId)
            : null;

        // Fall back to first active workspace
        if (! $workspace) {
            /** @var Workspace|null $workspace */
            $workspace = Workspace::query()->where('user_id', $user->id)->whereNull('archived_at')->first();
            if ($workspace) {
                $request->session()->put('current_workspace_id', $workspace->id);
            }
        }

        $request->attributes->set('current_workspace', $workspace);

        return $next($request);
    }
}
