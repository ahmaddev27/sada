<?php

// CON-08

namespace App\Actions\Social;

use App\Models\SocialAccount;
use App\Services\Meta\MetaOAuthService;

class DisconnectSocialAccountAction
{
    public function __construct(private readonly MetaOAuthService $meta) {}

    public function execute(SocialAccount $account): void
    {
        // Best-effort revoke on the provider side (do not throw on failure)
        try {
            if (in_array($account->provider, ['instagram', 'facebook'], true)) {
                $this->meta->revokeToken(
                    $account->access_token,
                    $account->provider_account_id
                );
            }
        } catch (\Throwable) {
            // Revocation failure should not prevent local disconnect
        }

        $account->delete();
    }
}
