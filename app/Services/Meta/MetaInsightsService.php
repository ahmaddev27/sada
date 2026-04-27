<?php

// ANL-07: fetch campaign performance insights from Meta Marketing API

namespace App\Services\Meta;

use App\Models\Campaign;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MetaInsightsService
{
    private string $graphBase;

    private const FIELDS = 'spend,reach,impressions,clicks,ctr,cpc,actions,action_values';

    public function __construct()
    {
        $v = (string) config('services.meta.graph_version', 'v21.0');
        $this->graphBase = "https://graph.facebook.com/{$v}";
    }

    /**
     * Pull latest insights for an active campaign from Meta Ads Insights API.
     *
     * @return array{spend: float, reach: int, impressions: int, clicks: int, ctr: float, cpc: float, roas: float}
     * @throws RuntimeException
     */
    public function fetchForCampaign(Campaign $campaign): array
    {
        if (! $campaign->meta_campaign_id) {
            throw new RuntimeException("Campaign #{$campaign->id} has no meta_campaign_id.");
        }

        if (! $campaign->socialAccount) {
            throw new RuntimeException("Campaign #{$campaign->id} has no linked social account.");
        }

        $response = Http::get(
            "{$this->graphBase}/{$campaign->meta_campaign_id}/insights",
            [
                'fields'       => self::FIELDS,
                'date_preset'  => 'lifetime',
                'access_token' => $campaign->socialAccount->access_token,
            ]
        )->throw()->json();

        $data = $response['data'][0] ?? null;

        if (! $data) {
            return $this->emptyInsights();
        }

        // Compute ROAS from action_values (purchase) / spend
        $roas = $this->computeRoas($data);

        return [
            'spend'       => (float) ($data['spend'] ?? 0),
            'reach'       => (int)   ($data['reach'] ?? 0),
            'impressions' => (int)   ($data['impressions'] ?? 0),
            'clicks'      => (int)   ($data['clicks'] ?? 0),
            'ctr'         => round((float) ($data['ctr'] ?? 0) / 100, 4), // Meta returns %, store as decimal
            'cpc'         => (float) ($data['cpc'] ?? 0),
            'roas'        => $roas,
        ];
    }

    /**
     * @param  array<string, mixed> $data
     */
    private function computeRoas(array $data): float
    {
        $spend = (float) ($data['spend'] ?? 0);
        if ($spend <= 0) {
            return 0.0;
        }

        $revenue = collect($data['action_values'] ?? [])
            ->where('action_type', 'offsite_conversion.fb_pixel_purchase')
            ->sum(fn ($a) => (float) ($a['value'] ?? 0));

        return $revenue > 0 ? round($revenue / $spend, 2) : 0.0;
    }

    /**
     * @return array{spend: float, reach: int, impressions: int, clicks: int, ctr: float, cpc: float, roas: float}
     */
    private function emptyInsights(): array
    {
        return ['spend' => 0.0, 'reach' => 0, 'impressions' => 0, 'clicks' => 0, 'ctr' => 0.0, 'cpc' => 0.0, 'roas' => 0.0];
    }
}
