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
