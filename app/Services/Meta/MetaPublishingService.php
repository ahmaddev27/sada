<?php

// SCH-04: publish posts to Meta Graph API (Facebook + Instagram)

namespace App\Services\Meta;

use App\Models\Post;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MetaPublishingService
{
    private string $graphBase;

    public function __construct()
    {
        $v = (string) config('services.meta.graph_version', 'v21.0');
        $this->graphBase = "https://graph.facebook.com/{$v}";
    }

    // SCH-04a: publish to Facebook page feed
    public function publishToFacebook(SocialAccount $account, Post $post): string
    {
        $pageId = $account->metadata['page_id'] ?? $account->provider_account_id;

        $payload = ['message' => $this->buildMessage($post)];

        if (! empty($post->metadata['media_url'])) {
            $payload['link'] = $post->metadata['media_url'];
        }

        $response = Http::post("{$this->base()}/{$pageId}/feed", array_merge($payload, [
            'access_token' => $account->access_token,
        ]))->throw()->json();

        return $response['id'] ?? '';
    }

    // SCH-04b: publish to Instagram (two-step: container → publish)
    public function publishToInstagram(SocialAccount $account, Post $post): string
    {
        $igAccountId = $account->provider_account_id;

        // Step 1: create media container
        $mediaPayload = [
            'caption'      => $this->buildMessage($post),
            'access_token' => $account->access_token,
        ];

        if (! empty($post->metadata['media_url'])) {
            $mediaPayload['image_url'] = $post->metadata['media_url'];
        } else {
            // Text-only — Instagram requires media, use a placeholder approach or skip
            throw new RuntimeException('Instagram requires a media attachment.');
        }

        $container = Http::post("{$this->base()}/{$igAccountId}/media", $mediaPayload)
            ->throw()->json();

        $containerId = $container['id'] ?? null;

        if (! $containerId) {
            throw new RuntimeException('Failed to create Instagram media container.');
        }

        // Step 2: publish container
        $result = Http::post("{$this->base()}/{$igAccountId}/media_publish", [
            'creation_id'  => $containerId,
            'access_token' => $account->access_token,
        ])->throw()->json();

        return $result['id'] ?? '';
    }

    public function publish(SocialAccount $account, Post $post): string
    {
        return match ($post->platform) {
            'facebook'  => $this->publishToFacebook($account, $post),
            'instagram' => $this->publishToInstagram($account, $post),
            default     => throw new RuntimeException("Unsupported platform: {$post->platform}"),
        };
    }

    private function buildMessage(Post $post): string
    {
        $tags = is_array($post->hashtags) && count($post->hashtags)
            ? "\n\n" . implode(' ', $post->hashtags)
            : '';

        return $post->content . $tags;
    }

    private function base(): string
    {
        return $this->graphBase;
    }
}
