<?php

// CON-07

namespace App\Actions\Social;

use App\Models\SocialAccount;
use App\Services\Meta\MetaOAuthService;
use Carbon\Carbon;

class RefreshSocialTokenAction
{
    public function __construct(private readonly MetaOAuthService $meta) {}

    public function execute(SocialAccount $account): SocialAccount
    {
        $refreshed = match ($account->provider) {
            'instagram', 'facebook' => $this->meta->refreshToken($account->access_token),
            default                 => throw new \LogicException("Token refresh not supported for {$account->provider}"),
        };

        $account->update([
            'access_token'     => $refreshed['access_token'],
            'token_expires_at' => isset($refreshed['expires_in'])
                ? Carbon::now()->addSeconds($refreshed['expires_in'])
                : null,
            'status'           => 'healthy',
        ]);

        return $account;
    }
}
