<?php

// ADS-07: submit campaign to Meta Marketing API

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\Meta\MetaAdsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SubmitCampaignToMetaJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly Campaign $campaign,
    ) {}

    public function handle(MetaAdsService $meta): void
    {
        $campaign = $this->campaign->fresh(['socialAccount']);

        if (! $campaign) {
            Log::warning('SubmitCampaignToMetaJob: campaign not found', ['id' => $this->campaign->id]);
            return;
        }

        if (! $campaign->socialAccount) {
            throw new RuntimeException(
                "الحملة #{$campaign->id} لا تحتوي على حساب اجتماعي مرتبط."
            );
        }

        $ids = $meta->submit($campaign, $campaign->socialAccount);

        $campaign->update(array_merge($ids, ['status' => 'active']));

        Log::info("Campaign {$campaign->id} submitted to Meta successfully", $ids);
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Campaign {$this->campaign->id} failed Meta submission", [
            'error' => $exception->getMessage(),
        ]);

        $this->campaign->update(['status' => 'rejected']);
    }
}
