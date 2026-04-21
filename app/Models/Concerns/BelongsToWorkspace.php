<?php

namespace App\Models\Concerns;

use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// WS-03: multi-tenancy via workspace_id on every tenant-scoped table
trait BelongsToWorkspace
{
    public static function bootBelongsToWorkspace(): void
    {
        static::addGlobalScope('workspace', function (Builder $query) {
            if ($workspaceId = session('current_workspace_id')) {
                $query->where(static::make()->getTable() . '.workspace_id', $workspaceId);
            }
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public static function withoutWorkspaceScope(): Builder
    {
        return static::withoutGlobalScope('workspace');
    }
}
