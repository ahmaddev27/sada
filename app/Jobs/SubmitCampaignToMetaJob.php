<?php

// ADS-07: submits campaign to Meta Marketing API
// Stubbed — pending Meta App Review approval
// When approved, implement MetaAdsClient here

namespace App\Jobs;

use App\Models\Campaign;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubmitCampaignToMetaJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly Campaign $campaign,
    ) {}

    public function handle(): void
    {
        // TODO: implement MetaAdsClient once Meta App Review is approved
        Log::info("Campaign {$this->campaign->id} queued for Meta submission", [
            'campaign_name' => $this->campaign->name,
            'platform'      => $this->campaign->platform,
            'objective'     => $this->campaign->objective,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Campaign {$this->campaign->id} failed Meta submission", [
            'error' => $exception->getMessage(),
        ]);

        $this->campaign->update(['status' => 'rejected']);
    }
}
