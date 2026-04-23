<?php

// CON-12: Snapchat OAuth 2.0 — authorization code flow (Marketing API)

namespace App\Services\Snapchat;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SnapchatOAuthService
{
    private const AUTH_URL  = 'https://accounts.snapchat.com/login/oauth2/authorize';
    private const TOKEN_URL = 'https://accounts.snapchat.com/login/oauth2/access_token';
    private const API_BASE  = 'https://adsapi.snapchat.com/v1';

    // Snapchat API does NOT support organic publishing — ads only
    private const SCOPES = [
        'snapchat-marketing-api',
        'snapchat-profile-api',
    ];

    public function redirectUrl(string $state): string
    {
        $params = http_build_query([
            'client_id'     => config('services.snapchat.client_id'),
            'redirect_uri'  => $this->callbackUrl(),
            'response_type' => 'code',
            'scope'         => implode(' ', self::SCOPES),
            'state'         => $state,
        ]);

        return self::AUTH_URL . '?' . $params;
    }

    /**
     * Exchange authorization code for access + refresh tokens.
     *
     * @return array{access_token: string, expires_in: int, refresh_token: string}
     */
    public function exchangeCode(string $code): array
    {
        $response = Http::withBasicAuth(
            config('services.snapchat.client_id'),
            config('services.snapchat.client_secret'),
        )
            ->asForm()
            ->post(self::TOKEN_URL, [
                'grant_type'   => 'authorization_code',
                'code'         => $code,
                'redirect_uri' => $this->callbackUrl(),
            ])->throw()->json();

        $this->assertNoError($response);

        return [
            'access_token'  => $response['access_token'],
            'expires_in'    => $response['expires_in'] ?? 1800,
            'refresh_token' => $response['refresh_token'] ?? '',
        ];
    }

    /**
     * Refresh an expired access token.
     *
     * @return array{access_token: string, expires_in: int, refresh_token: string}
     */
    public function refreshToken(string $refreshToken): array
    {
        $response = Http::withBasicAuth(
            config('services.snapchat.client_id'),
            config('services.snapchat.client_secret'),
        )
            ->asForm()
            ->post(self::TOKEN_URL, [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $refreshToken,
            ])->throw()->json();

        $this->assertNoError($response);

        return [
            'access_token'  => $response['access_token'],
            'expires_in'    => $response['expires_in'] ?? 1800,
            'refresh_token' => $response['refresh_token'] ?? $refreshToken,
        ];
    }

    /**
     * Fetch basic user info from the Marketing API /me endpoint.
     *
     * @return array{id: string, display_name: string, email: string|null}
     */
    public function userInfo(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get(self::API_BASE . '/me')->throw()->json();

        $this->assertNoError($response);

        $me = $response['me'] ?? [];

        return [
            'id'           => $me['id'] ?? '',
            'display_name' => $me['display_name'] ?? ($me['email'] ?? 'Snapchat User'),
            'email'        => $me['email'] ?? null,
        ];
    }

    private function callbackUrl(): string
    {
        return route('social.callback', ['provider' => 'snapchat']);
    }

    /** @param array<string, mixed> $response */
    private function assertNoError(array $response): void
    {
        if (isset($response['error'])) {
            $msg = $response['error_description'] ?? $response['error'];
            throw new RuntimeException("Snapchat OAuth error: {$msg}");
        }
    }
}
