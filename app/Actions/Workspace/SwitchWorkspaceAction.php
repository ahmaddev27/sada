<?php

namespace App\Actions\Workspace;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;

// WS-02: switch active workspace stored in session
class SwitchWorkspaceAction
{
    public function execute(Request $request, User $user, int $workspaceId): Workspace
    {
        /** @var Workspace $workspace */
        $workspace = Workspace::query()
            ->where('user_id', $user->id)
            ->whereNull('archived_at')
            ->findOrFail($workspaceId);

        $request->session()->put('current_workspace_id', $workspace->id);

        return $workspace;
    }
}
