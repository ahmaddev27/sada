<?php

namespace App\Actions\Workspace;

use App\Models\BrandIdentity;
use App\Models\Workspace;

// BI-01 → BI-04
class UpdateBrandIdentityAction
{
    public function execute(
        Workspace $workspace,
        ?string $description = null,
        ?string $tone = null,
        ?string $targetAudience = null,
        array $bannedWords = [],
        array $examplePosts = [],
    ): BrandIdentity {
        // BI-03: cap at 10 banned words
        $bannedWords = array_slice(array_unique($bannedWords), 0, 10);

        // BI-04: cap at 5 example posts
        $examplePosts = array_slice($examplePosts, 0, 5);

        $data = [
            'workspace_id'    => $workspace->id,
            'description'     => $description,
            'tone'            => $tone,
            'target_audience' => $targetAudience,
            'banned_words'    => $bannedWords,
            'example_posts'   => $examplePosts,
        ];

        /** @var BrandIdentity $brand */
        $brand = BrandIdentity::withoutWorkspaceScope()
            ->updateOrCreate(['workspace_id' => $workspace->id], $data);

        return $brand;
    }
}
