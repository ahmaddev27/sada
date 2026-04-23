<?php

// CON-13: X (Twitter) OAuth 2.0 with PKCE — authorization code flow

namespace App\Services\X;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class XOAuthService
{
    private const AUTH_URL  = 'https://twitter.com/i/oauth2/authorize';
    private const TOKEN_URL = 'https://api.x.com/2/oauth2/token';
    private const API_BASE  = 'https://api.x.com/2';

    private const SCOPES = [
        'tweet.read',
        'tweet.write',
        'users.read',
        'offline.access',
    ];

    /**
     * Generate PKCE verifier+challenge, store verifier in session, return auth URL.
     */
    public function redirectUrl(string $state): string
    {
        [$verifier, $challenge] = $this->generatePkce();

        session(['x_pkce_verifier' => $verifier]);

        $params = http_build_query([
            'client_id'             => config('services.x.client_id'),
            'redirect_uri'          => $this->callbackUrl(),
            'response_type'         => 'code',
            'scope'                 => implode(' ', self::SCOPES),
            'state'                 => $state,
            'code_challenge'        => $challenge,
            'code_challenge_method' => 'S256',
        ]);

        return self::AUTH_URL . '?' . $params;
    }

    /**
     * Exchange authorization code for access + refresh tokens.
     *
     * @return array{access_token: string, expires_in: int, refresh_token: string}
     */
    public function exchangeCode(string $code, string $codeVerifier): array
    {
        $response = Http::withBasicAuth(
            config('services.x.client_id'),
            config('services.x.client_secret'),
        )
            ->asForm()
            ->post(self::TOKEN_URL, [
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => $this->callbackUrl(),
                'code_verifier' => $codeVerifier,
            ])->throw()->json();

        $this->assertNoError($response);

        return [
            'access_token'  => $response['access_token'],
            'expires_in'    => $response['expires_in'] ?? 7200,
            'refresh_token' => $response['refresh_token'] ?? '',
        ];
    }

    /**
     * Refresh an expired access token using the refresh token.
     *
     * @return array{access_token: string, expires_in: int, refresh_token: string}
     */
    public function refreshToken(string $refreshToken): array
    {
        $response = Http::withBasicAuth(
            config('services.x.client_id'),
            config('services.x.client_secret'),
        )
            ->asForm()
            ->post(self::TOKEN_URL, [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $refreshToken,
            ])->throw()->json();

        $this->assertNoError($response);

        return [
            'access_token'  => $response['access_token'],
            'expires_in'    => $response['expires_in'] ?? 7200,
            'refresh_token' => $response['refresh_token'] ?? $refreshToken,
        ];
    }

    /**
     * Fetch authenticated user info.
     *
     * @return array{id: string, name: string, username: string, profile_image_url: string|null}
     */
    public function userInfo(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get(self::API_BASE . '/users/me', [
                'user.fields' => 'id,name,username,profile_image_url',
            ])->throw()->json();

        $this->assertNoError($response);

        $data = $response['data'] ?? [];

        return [
            'id'                => $data['id'] ?? '',
            'name'              => $data['name'] ?? '',
            'username'          => $data['username'] ?? '',
            'profile_image_url' => $data['profile_image_url'] ?? null,
        ];
    }

    private function callbackUrl(): string
    {
        return route('social.callback', ['provider' => 'x']);
    }

    /** @return array{0: string, 1: string} [verifier, challenge] */
    private function generatePkce(): array
    {
        $verifier  = rtrim(strtr(base64_encode(random_bytes(96)), '+/', '-_'), '=');
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        return [$verifier, $challenge];
    }

    /** @param array<string, mixed> $response */
    private function assertNoError(array $response): void
    {
        if (isset($response['error'])) {
            $msg = $response['error_description'] ?? $response['error'];
            throw new RuntimeException("X OAuth error: {$msg}");
        }
    }
}
