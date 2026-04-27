<?php

// ANL-07: artisan command — dispatches SyncCampaignInsightsJob for all active campaigns

namespace App\Console\Commands;

use App\Jobs\SyncCampaignInsightsJob;
use App\Models\Campaign;
use Illuminate\Console\Command;

class SyncCampaignInsights extends Command
{
    protected $signature = 'campaigns:sync-insights';

    protected $description = 'Sync Meta Ads insights for all active/paused/completed campaigns';

    public function handle(): int
    {
        $campaigns = Campaign::withoutWorkspaceScope()
            ->whereIn('status', ['active', 'paused', 'completed'])
            ->whereNotNull('meta_campaign_id')
            ->with('socialAccount:id,access_token,workspace_id,provider')
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No campaigns to sync.');
            return self::SUCCESS;
        }

        foreach ($campaigns as $campaign) {
            SyncCampaignInsightsJob::dispatch($campaign);
        }

        $this->info("Dispatched insights sync for {$campaigns->count()} campaign(s).");

        return self::SUCCESS;
    }
}
