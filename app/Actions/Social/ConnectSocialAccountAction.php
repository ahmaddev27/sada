<?php

// CON-01, CON-02, CON-06

namespace App\Actions\Social;

use App\Models\SocialAccount;
use App\Models\Workspace;
use Carbon\Carbon;

class ConnectSocialAccountAction
{
    /**
     * Upsert a social account record for the workspace.
     *
     * @param array{
     *   provider: string,
     *   provider_account_id: string,
     *   account_name: string,
     *   account_picture_url: string|null,
     *   access_token: string,
     *   refresh_token: string|null,
     *   expires_in: int|null,
     *   scopes: string[],
     *   metadata: array<string, mixed>,
     * } $data
     */
    public function execute(Workspace $workspace, array $data): SocialAccount
    {
        $expiresAt = isset($data['expires_in'])
            ? Carbon::now()->addSeconds($data['expires_in'])
            : null;

        /** @var SocialAccount */
        $account = SocialAccount::withoutWorkspaceScope()->updateOrCreate(
            [
                'workspace_id'        => $workspace->id,
                'provider'            => $data['provider'],
                'provider_account_id' => $data['provider_account_id'],
            ],
            [
                'account_name'        => $data['account_name'],
                'account_picture_url' => $data['account_picture_url'] ?? null,
                'access_token'        => $data['access_token'],   // encrypted cast handles AES-256
                'refresh_token'       => $data['refresh_token'] ?? null,
                'token_expires_at'    => $expiresAt,
                'status'              => 'healthy',
                'scopes'              => $data['scopes'],
                'metadata'            => $data['metadata'],
            ]
        );

        return $account;
    }
}
