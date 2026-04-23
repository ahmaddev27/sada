<?php

// ADS-01→ADS-07

namespace App\Actions\Campaigns;

use App\Models\Campaign;

class UpdateCampaignAction
{
    /**
     * Update a campaign.
     *
     * Draft campaigns may be fully updated.
     * Active campaigns may only update name, budget_amount, and ends_at.
     *
     * @param array<string, mixed> $data
     */
    public function execute(Campaign $campaign, array $data): Campaign
    {
        $allowedData = $campaign->isDraft()
            ? $data
            : $this->filterActiveFields($data);

        $campaign->update($allowedData);

        return $campaign->fresh() ?? $campaign;
    }

    /**
     * Active campaigns have a restricted field whitelist per Meta policy.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function filterActiveFields(array $data): array
    {
        return array_intersect_key($data, array_flip(['name', 'budget_amount', 'ends_at']));
    }
}
