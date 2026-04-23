<?php

// CON-07

namespace App\Actions\Social;

use App\Models\SocialAccount;
use App\Services\Meta\MetaOAuthService;
use App\Services\TikTok\TikTokOAuthService;
use Carbon\Carbon;

class RefreshSocialTokenAction
{
    public function __construct(
        private readonly MetaOAuthService $meta,
        private readonly TikTokOAuthService $tiktok,
    ) {}

    public function execute(SocialAccount $account): SocialAccount
    {
        $refreshed = match ($account->provider) {
            'instagram', 'facebook' => $this->meta->refreshToken($account->access_token),
            'tiktok'                => $this->refreshTikTok($account),
            default                 => throw new \LogicException("Token refresh not supported for {$account->provider}"),
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

    /** @return array{access_token: string, expires_in: int, refresh_token: string} */
    private function refreshTikTok(SocialAccount $account): array
    {
        if (! $account->refresh_token) {
            throw new \LogicException('No TikTok refresh token available. User must re-authenticate.');
        }

        return $this->tiktok->refreshToken($account->refresh_token);
    }
}
