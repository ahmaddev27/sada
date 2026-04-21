<?php

namespace App\Actions\Workspace;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Validation\ValidationException;

// WS-01
class CreateWorkspaceAction
{
    private const FREE_TIER_LIMIT = 10;

    public function execute(
        User $user,
        string $name,
        ?string $businessType = null,
        array $countries = [],
        string $defaultDialect = 'sa',
    ): Workspace {
        // WS-01: free tier limit
        if ($user->activeWorkspaces()->count() >= self::FREE_TIER_LIMIT) {
            throw ValidationException::withMessages([
                'name' => 'وصلت إلى الحد الأقصى المسموح به من مساحات العمل (١٠ مساحات).',
            ]);
        }

        $workspace = Workspace::create([
            'user_id'         => $user->id,
            'name'            => $name,
            'business_type'   => $businessType,
            'countries'       => $countries ?: ['sa'],
            'default_dialect' => $defaultDialect,
        ]);

        // Auto-create empty brand identity (BI-01)
        $workspace->brandIdentity()->create([]);

        return $workspace;
    }
}
