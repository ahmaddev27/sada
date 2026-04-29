<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class FeatureFlagService
{
    private const CACHE_KEY = 'admin:feature_flags';

    public function isEnabled(string $key): bool
    {
        $flags = Cache::get(self::CACHE_KEY, []);
        $flag  = collect($flags)->firstWhere('key', $key);

        return $flag !== null ? (bool) $flag['enabled'] : $this->defaultFor($key);
    }

    /**
     * All flags as a key→bool map for frontend consumption.
     *
     * @return array<string, bool>
     */
    public function all(): array
    {
        $keys = [
            'ai_generation', 'paid_campaigns', 'tiktok_integration',
            'snapchat_integration', 'x_integration', 'linkedin_integration',
            'seasonal_engine', 'analytics_ai', 'billing',
        ];

        return array_combine($keys, array_map(fn ($k) => $this->isEnabled($k), $keys));
    }

    /** Default used before an admin has ever saved flags to cache. */
    private function defaultFor(string $key): bool
    {
        return match ($key) {
            'tiktok_integration',
            'snapchat_integration',
            'x_integration',
            'linkedin_integration' => false,
            default                => true,
        };
    }
}
