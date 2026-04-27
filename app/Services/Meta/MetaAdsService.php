<?php

// ADS-07→ADS-11: Meta Marketing API — campaign/adset/ad creation

namespace App\Services\Meta;

use App\Models\Campaign;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MetaAdsService
{
    private string $graphBase;

    // Country codes (ISO 2) map — Meta uses uppercase ISO-2
    private const COUNTRY_MAP = [
        'sa' => 'SA', 'ae' => 'AE', 'kw' => 'KW',
        'qa' => 'QA', 'bh' => 'BH', 'om' => 'OM',
    ];

    // SRS objective → Meta campaign objective
    private const OBJECTIVE_MAP = [
        'awareness'    => 'BRAND_AWARENESS',
        'traffic'      => 'LINK_CLICKS',
        'engagement'   => 'POST_ENGAGEMENT',
        'conversions'  => 'CONVERSIONS',
        'app_installs' => 'APP_INSTALLS',
        'video_views'  => 'VIDEO_VIEWS',
    ];

    // Gender: 0 = all, 1 = male, 2 = female
    private const GENDER_MAP = [
        'all'    => [],
        'male'   => [1],
        'female' => [2],
    ];

    public function __construct()
    {
        $v = (string) config('services.meta.graph_version', 'v21.0');
        $this->graphBase = "https://graph.facebook.com/{$v}";
    }

    /**
     * Full campaign creation flow:
     *   1. Create Meta campaign
     *   2. Create Ad Set with targeting + budget
     *   3. Create Ad Creative
     *   4. Create Ad
     *
     * Returns array with meta_campaign_id, meta_adset_id, meta_ad_id.
     *
     * @return array{meta_campaign_id: string, meta_adset_id: string, meta_ad_id: string}
     * @throws RuntimeException on API error
     */
    public function submit(Campaign $campaign, SocialAccount $account): array
    {
        $adAccountId = $this->resolveAdAccountId($account);
        $accessToken = $account->access_token;

        $metaCampaignId = $this->createCampaign($adAccountId, $accessToken, $campaign);
        $metaAdsetId    = $this->createAdSet($adAccountId, $accessToken, $campaign, $metaCampaignId);
        $creativeId     = $this->createAdCreative($adAccountId, $accessToken, $campaign, $account);
        $metaAdId       = $this->createAd($adAccountId, $accessToken, $campaign, $metaAdsetId, $creativeId);

        return [
            'meta_campaign_id' => $metaCampaignId,
            'meta_adset_id'    => $metaAdsetId,
            'meta_ad_id'       => $metaAdId,
        ];
    }

    // ── Step 1: Campaign ──────────────────────────────────────────────────────

    private function createCampaign(string $adAccountId, string $token, Campaign $campaign): string
    {
        $objective = self::OBJECTIVE_MAP[$campaign->objective] ?? 'POST_ENGAGEMENT';

        $response = Http::post("{$this->graphBase}/act_{$adAccountId}/campaigns", [
            'name'             => $campaign->name,
            'objective'        => $objective,
            'status'           => 'ACTIVE',
            'special_ad_categories' => [],
            'access_token'     => $token,
        ])->throw()->json();

        return $this->extractId($response, 'campaign');
    }

    // ── Step 2: Ad Set ────────────────────────────────────────────────────────

    private function createAdSet(string $adAccountId, string $token, Campaign $campaign, string $metaCampaignId): string
    {
        $targeting = $this->buildTargeting($campaign);

        // Meta expects budget in cents
        $budgetField  = $campaign->budget_type === 'daily' ? 'daily_budget' : 'lifetime_budget';
        $budgetCents  = (int) round((float) $campaign->budget_amount * 100);

        $payload = [
            'name'               => $campaign->name . ' — Ad Set',
            'campaign_id'        => $metaCampaignId,
            'billing_event'      => 'IMPRESSIONS',
            'optimization_goal'  => $this->resolveOptimizationGoal($campaign->objective),
            $budgetField         => $budgetCents,
            'targeting'          => json_encode($targeting),
            'start_time'         => $campaign->starts_at?->toIso8601String(),
            'end_time'           => $campaign->ends_at?->toIso8601String(),
            'status'             => 'ACTIVE',
            'access_token'       => $token,
        ];

        if ($campaign->budget_type === 'lifetime' && $campaign->ends_at) {
            $payload['end_time'] = $campaign->ends_at->toIso8601String();
        }

        $response = Http::post("{$this->graphBase}/act_{$adAccountId}/adsets", $payload)
            ->throw()->json();

        return $this->extractId($response, 'adset');
    }

    // ── Step 3: Ad Creative ───────────────────────────────────────────────────

    private function createAdCreative(string $adAccountId, string $token, Campaign $campaign, SocialAccount $account): string
    {
        $pageId = $account->metadata['page_id'] ?? $account->provider_account_id;

        $creative = [
            'name'          => $campaign->name . ' — Creative',
            'object_story_spec' => [
                'page_id' => $pageId,
                'link_data' => [
                    'message'     => $campaign->ad_copy ?? '',
                    'name'        => $campaign->ad_headline ?? $campaign->name,
                    'description' => $campaign->ad_description ?? '',
                    'link'        => $account->metadata['website_url'] ?? 'https://sada.sa',
                ],
            ],
            'access_token' => $token,
        ];

        // Attach existing post as creative if available (ADS-06)
        if ($campaign->post_id && $campaign->post) {
            $creative['object_story_spec'] = [
                'page_id'     => $pageId,
                'post_id'     => $campaign->post->metadata['meta_post_id'] ?? null,
            ];
        }

        $response = Http::post("{$this->graphBase}/act_{$adAccountId}/adcreatives", $creative)
            ->throw()->json();

        return $this->extractId($response, 'adcreative');
    }

    // ── Step 4: Ad ────────────────────────────────────────────────────────────

    private function createAd(string $adAccountId, string $token, Campaign $campaign, string $adsetId, string $creativeId): string
    {
        $response = Http::post("{$this->graphBase}/act_{$adAccountId}/ads", [
            'name'       => $campaign->name . ' — Ad',
            'adset_id'   => $adsetId,
            'creative'   => json_encode(['creative_id' => $creativeId]),
            'status'     => 'ACTIVE',
            'access_token' => $token,
        ])->throw()->json();

        return $this->extractId($response, 'ad');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    private function buildTargeting(Campaign $campaign): array
    {
        $countries = array_values(array_filter(
            array_map(fn ($c) => self::COUNTRY_MAP[$c] ?? null, $campaign->target_countries ?? [])
        ));

        $targeting = [
            'geo_locations' => ['countries' => $countries ?: ['SA']],
            'age_min'       => $campaign->target_age_min ?? 18,
            'age_max'       => $campaign->target_age_max ?? 65,
        ];

        $genders = self::GENDER_MAP[$campaign->target_gender ?? 'all'] ?? [];
        if (! empty($genders)) {
            $targeting['genders'] = $genders;
        }

        if (! empty($campaign->target_interests)) {
            $targeting['flexible_spec'] = [
                ['interests' => array_map(
                    fn ($id) => ['id' => $id],
                    $campaign->target_interests
                )],
            ];
        }

        return $targeting;
    }

    private function resolveOptimizationGoal(string $objective): string
    {
        return match ($objective) {
            'awareness'    => 'REACH',
            'traffic'      => 'LINK_CLICKS',
            'engagement'   => 'POST_ENGAGEMENT',
            'conversions'  => 'OFFSITE_CONVERSIONS',
            'app_installs' => 'APP_INSTALLS',
            'video_views'  => 'VIDEO_VIEWS',
            default        => 'POST_ENGAGEMENT',
        };
    }

    private function resolveAdAccountId(SocialAccount $account): string
    {
        $id = $account->metadata['ad_account_id'] ?? null;

        if (! $id) {
            throw new RuntimeException(
                "الحساب #{$account->id} لا يحتوي على ad_account_id. يرجى ربط حساب الإعلانات أولاً."
            );
        }

        return ltrim((string) $id, 'act_');
    }

    private function extractId(mixed $response, string $entity): string
    {
        $id = $response['id'] ?? null;

        if (! $id) {
            throw new RuntimeException(
                "Meta API لم يُرجع ID للـ {$entity}. Response: " . json_encode($response)
            );
        }

        return (string) $id;
    }
}
