<?php

// CON-11, SCH-04c: publish posts to TikTok via Content Posting API v2

namespace App\Services\TikTok;

use App\Models\Post;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TikTokPublishingService
{
    private const API_BASE = 'https://open.tiktokapis.com/v2';

    public function publish(SocialAccount $account, Post $post): string
    {
        return match ($post->content_type) {
            'reel', 'post' => $this->publishVideo($account, $post),
            'story'        => $this->publishPhoto($account, $post),
            default        => $this->publishVideo($account, $post),
        };
    }

    // SCH-04c: video post via PULL_FROM_URL (TikTok fetches media from URL)
    private function publishVideo(SocialAccount $account, Post $post): string
    {
        $mediaUrl = $post->metadata['media_url'] ?? null;

        if (! $mediaUrl) {
            throw new RuntimeException('TikTok video posts require a media_url in post metadata.');
        }

        $caption = $this->buildCaption($post);

        $response = Http::withToken($account->access_token)
            ->post(self::API_BASE . '/post/publish/video/init/', [
                'post_info' => [
                    'title'         => mb_substr($caption, 0, 2200),
                    'privacy_level' => 'PUBLIC_TO_EVERYONE',
                    'disable_duet'  => false,
                    'disable_stitch'=> false,
                    'disable_comment'=> false,
                ],
                'source_info' => [
                    'source'    => 'PULL_FROM_URL',
                    'video_url' => $mediaUrl,
                ],
            ])->throw()->json();

        $this->assertNoError($response);

        return $response['data']['publish_id'] ?? '';
    }

    // Photo post via PULL_FROM_URL (images array)
    private function publishPhoto(SocialAccount $account, Post $post): string
    {
        $mediaUrl = $post->metadata['media_url'] ?? null;

        if (! $mediaUrl) {
            throw new RuntimeException('TikTok photo posts require a media_url in post metadata.');
        }

        $caption = $this->buildCaption($post);

        $response = Http::withToken($account->access_token)
            ->post(self::API_BASE . '/post/publish/content/init/', [
                'post_info' => [
                    'title'         => mb_substr($caption, 0, 2200),
                    'privacy_level' => 'PUBLIC_TO_EVERYONE',
                    'disable_comment'=> false,
                ],
                'source_info' => [
                    'source'      => 'PULL_FROM_URL',
                    'photo_images'=> [$mediaUrl],
                    'photo_cover_index' => 0,
                ],
            ])->throw()->json();

        $this->assertNoError($response);

        return $response['data']['publish_id'] ?? '';
    }

    private function buildCaption(Post $post): string
    {
        $tags = is_array($post->hashtags) && count($post->hashtags)
            ? "\n\n" . implode(' ', $post->hashtags)
            : '';

        return $post->content . $tags;
    }

    /** @param array<string, mixed> $response */
    private function assertNoError(array $response): void
    {
        $code = $response['error']['code'] ?? null;

        if ($code && $code !== 'ok') {
            $msg = $response['error']['message'] ?? 'TikTok Publishing API error';
            throw new RuntimeException("TikTok publish failed: {$msg}");
        }
    }
}
