<?php

// ADS-01→ADS-07

namespace App\Actions\Campaigns;

use App\Jobs\SubmitCampaignToMetaJob;
use App\Models\Campaign;
use App\Models\Workspace;

class CreateCampaignAction
{
    /**
     * Create a new campaign and optionally dispatch it for Meta submission.
     *
     * @param array<string, mixed> $data
     * @throws \RuntimeException if social_account_id does not belong to the workspace
     */
    public function execute(Workspace $workspace, array $data): Campaign
    {
        // Guard: social account must belong to this workspace
        if (isset($data['social_account_id'])) {
            $owned = $workspace->socialAccounts()
                ->where('id', $data['social_account_id'])
                ->exists();

            if (! $owned) {
                throw new \RuntimeException('الحساب الاجتماعي المحدد لا ينتمي إلى مساحة العمل الحالية.');
            }
        }

        $campaign = Campaign::create(array_merge($data, [
            'workspace_id' => $workspace->id,
        ]));

        // ADS-07: if not a draft, hand off to Meta (stubbed until App Review approval)
        if (! $campaign->isDraft()) {
            SubmitCampaignToMetaJob::dispatch($campaign);
        }

        return $campaign;
    }
}
