<?php

// CON-01, CON-02, CON-07

namespace App\Services\Meta;

use Illuminate\Support\Facades\Http;

class MetaOAuthService
{
    private const GRAPH_URL = 'https://graph.facebook.com/v21.0';
    private const OAUTH_URL = 'https://www.facebook.com/v21.0/dialog/oauth';
    private const TOKEN_URL = 'https://graph.facebook.com/v21.0/oauth/access_token';

    // Required scopes for IG Business + Facebook Pages (MVP)
    private const SCOPES = [
        'instagram_basic',
        'instagram_content_publish',
        'pages_manage_posts',
        'pages_read_engagement',
        'pages_show_list',
        'business_management',
    ];

    public function redirectUrl(string $state): string
    {
        $params = http_build_query([
            'client_id'     => config('services.meta.client_id'),
            'redirect_uri'  => $this->callbackUrl(),
            'scope'         => implode(',', self::SCOPES),
            'response_type' => 'code',
            'state'         => $state,
        ]);

        return self::OAUTH_URL . '?' . $params;
    }

    /**
     * CON-01, CON-02: exchange code → short-lived → long-lived token
     *
     * @return array{access_token: string, expires_in: int}
     */
    public function exchangeCode(string $code): array
    {
        $short = $this->fetchShortLivedToken($code);

        return $this->exchangeForLongLived($short['access_token']);
    }

    /**
     * CON-01: fetch Instagram Business accounts linked to a Facebook Page
     *
     * @return array<int, array{id: string, name: string, picture: string|null, page_id: string}>
     */
    public function instagramAccounts(string $accessToken): array
    {
        $pages = $this->graphGet('/me/accounts', $accessToken, ['fields' => 'id,name,instagram_business_account{id,name,profile_picture_url}']);

        $accounts = [];
        foreach (($pages['data'] ?? []) as $page) {
            $igAccount = $page['instagram_business_account'] ?? null;
            if ($igAccount) {
                $accounts[] = [
                    'id'      => $igAccount['id'],
                    'name'    => $igAccount['name'] ?? $page['name'],
                    'picture' => $igAccount['profile_picture_url'] ?? null,
                    'page_id' => $page['id'],
                ];
            }
        }

        return $accounts;
    }

    /**
     * CON-02: fetch Facebook Pages
     *
     * @return array<int, array{id: string, name: string, picture: string|null, access_token: string}>
     */
    public function facebookPages(string $accessToken): array
    {
        $response = $this->graphGet('/me/accounts', $accessToken, [
            'fields' => 'id,name,picture,access_token',
        ]);

        return array_map(fn (array $p) => [
            'id'           => $p['id'],
            'name'         => $p['name'],
            'picture'      => $p['picture']['data']['url'] ?? null,
            'access_token' => $p['access_token'],
        ], $response['data'] ?? []);
    }

    /**
     * CON-07: refresh a long-lived token before expiry
     *
     * @return array{access_token: string, expires_in: int|null}
     */
    public function refreshToken(string $accessToken): array
    {
        $response = Http::get(self::TOKEN_URL, [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => config('services.meta.client_id'),
            'client_secret'     => config('services.meta.client_secret'),
            'fb_exchange_token' => $accessToken,
        ])->throw()->json();

        return [
            'access_token' => $response['access_token'],
            'expires_in'   => $response['expires_in'] ?? null,
        ];
    }

    // CON-08: revoke token on Meta side
    public function revokeToken(string $accessToken, string $providerAccountId): void
    {
        Http::delete(self::GRAPH_URL . "/{$providerAccountId}/permissions", [
            'access_token' => $accessToken,
        ]);
    }

    /** @return array{access_token: string, token_type: string} */
    private function fetchShortLivedToken(string $code): array
    {
        return Http::get(self::TOKEN_URL, [
            'client_id'     => config('services.meta.client_id'),
            'client_secret' => config('services.meta.client_secret'),
            'redirect_uri'  => $this->callbackUrl(),
            'code'          => $code,
        ])->throw()->json();
    }

    /** @return array{access_token: string, expires_in: int} */
    private function exchangeForLongLived(string $shortToken): array
    {
        $response = Http::get(self::TOKEN_URL, [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => config('services.meta.client_id'),
            'client_secret'     => config('services.meta.client_secret'),
            'fb_exchange_token' => $shortToken,
        ])->throw()->json();

        return [
            'access_token' => $response['access_token'],
            'expires_in'   => $response['expires_in'] ?? 5184000, // 60 days default
        ];
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function graphGet(string $path, string $accessToken, array $params = []): array
    {
        return Http::get(self::GRAPH_URL . $path, array_merge($params, [
            'access_token' => $accessToken,
        ]))->throw()->json();
    }

    private function callbackUrl(): string
    {
        return route('social.callback', ['provider' => 'meta']);
    }
}
