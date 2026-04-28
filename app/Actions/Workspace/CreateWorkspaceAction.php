<?php

namespace App\Actions\Workspace;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Validation\ValidationException;

// WS-01
class CreateWorkspaceAction
{
    private const FREE_TIER_LIMIT = 10;

    /**
     * @param array<int, string> $countries
     */
    public function execute(
        User $user,
        string $name,
        string $entityType = 'business',
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
            'entity_type'     => $entityType,
            'business_type'   => $businessType,
            'countries'       => $countries ?: ['sa'],
            'default_dialect' => $defaultDialect,
        ]);

        // Auto-create empty brand identity (BI-01)
        $workspace->brandIdentity()->create([]);

        return $workspace;
    }
}
