<?php

namespace App\Services\Publishing;

use App\Models\Post;
use App\Models\SocialAccount;
use App\Services\Meta\MetaPublishingService;
use App\Services\TikTok\TikTokPublishingService;
use RuntimeException;

class PublishingRouter
{
    public function __construct(
        private readonly MetaPublishingService $meta,
        private readonly TikTokPublishingService $tiktok,
    ) {}

    public function publish(SocialAccount $account, Post $post): string
    {
        return match ($account->provider) {
            'instagram', 'facebook' => $this->meta->publish($account, $post),
            'tiktok'                => $this->tiktok->publish($account, $post),
            default                 => throw new RuntimeException("Unsupported provider: {$account->provider}"),
        };
    }
}
