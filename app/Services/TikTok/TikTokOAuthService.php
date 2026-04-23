<?php

// CON-11: TikTok OAuth 2.0 — authorization code flow

namespace App\Services\TikTok;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TikTokOAuthService
{
    private const AUTH_URL  = 'https://www.tiktok.com/v2/auth/authorize/';
    private const TOKEN_URL = 'https://open.tiktokapis.com/v2/oauth/token/';
    private const API_BASE  = 'https://open.tiktokapis.com/v2';

    private const SCOPES = [
        'user.info.basic',
        'video.upload',
        'video.publish',
    ];

    public function redirectUrl(string $state): string
    {
        $params = http_build_query([
            'client_key'    => config('services.tiktok.client_key'),
            'scope'         => implode(',', self::SCOPES),
            'response_type' => 'code',
            'redirect_uri'  => $this->callbackUrl(),
            'state'         => $state,
        ]);

        return self::AUTH_URL . '?' . $params;
    }

    /**
     * Exchange authorization code for access + refresh tokens.
     *
     * @return array{open_id: string, access_token: string, expires_in: int, refresh_token: string, refresh_expires_in: int}
     */
    public function exchangeCode(string $code): array
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_key'    => config('services.tiktok.client_key'),
            'client_secret' => config('services.tiktok.client_secret'),
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $this->callbackUrl(),
        ])->throw()->json();

        $this->assertNoError($response);

        return [
            'open_id'           => $response['open_id'],
            'access_token'      => $response['access_token'],
            'expires_in'        => $response['expires_in'] ?? 86400,
            'refresh_token'     => $response['refresh_token'],
            'refresh_expires_in'=> $response['refresh_expires_in'] ?? 31536000,
        ];
    }

    /**
     * Refresh an expired access token using the refresh token.
     *
     * @return array{access_token: string, expires_in: int, refresh_token: string, refresh_expires_in: int}
     */
    public function refreshToken(string $refreshToken): array
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_key'    => config('services.tiktok.client_key'),
            'client_secret' => config('services.tiktok.client_secret'),
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
        ])->throw()->json();

        $this->assertNoError($response);

        return [
            'access_token'      => $response['access_token'],
            'expires_in'        => $response['expires_in'] ?? 86400,
            'refresh_token'     => $response['refresh_token'],
            'refresh_expires_in'=> $response['refresh_expires_in'] ?? 31536000,
        ];
    }

    /**
     * Fetch basic user info (display_name, avatar_url) for the connected account.
     *
     * @return array{open_id: string, display_name: string, avatar_url: string|null}
     */
    public function userInfo(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get(self::API_BASE . '/user/info/', [
                'fields' => 'open_id,display_name,avatar_url',
            ])->throw()->json();

        $this->assertNoError($response);

        $data = $response['data']['user'] ?? [];

        return [
            'open_id'      => $data['open_id'] ?? '',
            'display_name' => $data['display_name'] ?? '',
            'avatar_url'   => $data['avatar_url'] ?? null,
        ];
    }

    private function callbackUrl(): string
    {
        return route('social.callback', ['provider' => 'tiktok']);
    }

    /** @param array<string, mixed> $response */
    private function assertNoError(array $response): void
    {
        $error = $response['error'] ?? ($response['error_code'] ?? null);

        if ($error) {
            $msg = $response['error_description'] ?? ($response['message'] ?? 'TikTok API error');
            throw new RuntimeException("TikTok OAuth error: {$msg}");
        }
    }
}
