<?php

// ANL-07: sync Meta campaign insights

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\Meta\MetaInsightsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncCampaignInsightsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $backoff = 120;

    public function __construct(
        public readonly Campaign $campaign,
    ) {}

    public function handle(MetaInsightsService $meta): void
    {
        $campaign = $this->campaign->fresh(['socialAccount']);

        if (! $campaign || ! in_array($campaign->status, ['active', 'paused', 'completed'])) {
            return;
        }

        $insights = $meta->fetchForCampaign($campaign);

        $campaign->update([
            'insights'           => $insights,
            'insights_synced_at' => now(),
        ]);

        Log::info("Campaign {$campaign->id} insights synced", ['spend' => $insights['spend']]);
    }

    public function failed(Throwable $exception): void
    {
        Log::warning("Campaign {$this->campaign->id} insights sync failed", [
            'error' => $exception->getMessage(),
        ]);
    }
}
