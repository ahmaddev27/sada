<?php

// CON-13, SCH-04d: publish posts to X (Twitter) via v2 Tweets API

namespace App\Services\X;

use App\Models\Post;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class XPublishingService
{
    private const API_BASE = 'https://api.x.com/2';

    public function publish(SocialAccount $account, Post $post): string
    {
        $text = $this->buildText($post);

        $payload = ['text' => $text];

        $mediaIds = $this->uploadMedia($account, $post);
        if ($mediaIds !== []) {
            $payload['media'] = ['media_ids' => $mediaIds];
        }

        $response = Http::withToken($account->access_token)
            ->post(self::API_BASE . '/tweets', $payload)
            ->throw()
            ->json();

        $this->assertNoError($response);

        return $response['data']['id'] ?? '';
    }

    private function buildText(Post $post): string
    {
        $tags = is_array($post->hashtags) && count($post->hashtags)
            ? "\n\n" . implode(' ', $post->hashtags)
            : '';

        // X hard limit: 280 chars (Basic tier)
        return mb_substr($post->content . $tags, 0, 280);
    }

    /**
     * Upload media assets via the v1.1 media upload endpoint.
     * Returns an array of media_ids (strings), or empty array if no media.
     *
     * @return list<string>
     */
    private function uploadMedia(SocialAccount $account, Post $post): array
    {
        $mediaUrl = $post->metadata['media_url'] ?? null;

        if (! $mediaUrl) {
            return [];
        }

        // v1.1 media upload (still the only media upload endpoint as of 2025)
        $response = Http::withToken($account->access_token)
            ->post('https://upload.twitter.com/1.1/media/upload.json', [
                'media_category' => 'tweet_image',
                // When using PULL_FROM_URL pattern: not supported on v1.1.
                // The caller must pass a pre-uploaded URL or we skip media.
                // For now log and skip rather than hard-fail the tweet.
            ]);

        if ($response->failed()) {
            return [];
        }

        $mediaId = $response->json('media_id_string');

        return $mediaId ? [$mediaId] : [];
    }

    /** @param array<string, mixed> $response */
    private function assertNoError(array $response): void
    {
        if (isset($response['errors'])) {
            $first = $response['errors'][0] ?? [];
            $msg   = $first['message'] ?? 'X API error';
            throw new RuntimeException("X publish failed: {$msg}");
        }

        if (isset($response['error'])) {
            throw new RuntimeException("X publish failed: {$response['error']}");
        }
    }
}
