<?php

// CON-07

namespace App\Actions\Social;

use App\Models\SocialAccount;
use App\Services\Meta\MetaOAuthService;
use App\Services\Snapchat\SnapchatOAuthService;
use App\Services\TikTok\TikTokOAuthService;
use App\Services\X\XOAuthService;
use Carbon\Carbon;

class RefreshSocialTokenAction
{
    public function __construct(
        private readonly MetaOAuthService $meta,
        private readonly TikTokOAuthService $tiktok,
        private readonly SnapchatOAuthService $snapchat,
        private readonly XOAuthService $x,
    ) {}

    public function execute(SocialAccount $account): SocialAccount
    {
        $refreshed = match ($account->provider) {
            'instagram', 'facebook' => $this->meta->refreshToken($account->access_token),
            'tiktok'                => $this->refreshWithToken($account, 'TikTok', fn ($t) => $this->tiktok->refreshToken($t)),
            'snapchat'              => $this->refreshWithToken($account, 'Snapchat', fn ($t) => $this->snapchat->refreshToken($t)),
            default                 => $this->refreshWithToken($account, 'X', fn ($t) => $this->x->refreshToken($t)),
        };

        $account->update([
            'access_token'     => $refreshed['access_token'],
            'refresh_token'    => $refreshed['refresh_token'] ?? $account->refresh_token,
            'token_expires_at' => isset($refreshed['expires_in'])
                ? Carbon::now()->addSeconds($refreshed['expires_in'])
                : null,
            'status'           => 'healthy',
        ]);

        return $account;
    }

    /**
     * @param  callable(string): array{access_token: string, expires_in: int, refresh_token: string} $refreshFn
     * @return array{access_token: string, expires_in: int, refresh_token: string}
     */
    private function refreshWithToken(SocialAccount $account, string $providerName, callable $refreshFn): array
    {
        if (! $account->refresh_token) {
            throw new \LogicException("No {$providerName} refresh token available. User must re-authenticate.");
        }

        return $refreshFn($account->refresh_token);
    }
}
