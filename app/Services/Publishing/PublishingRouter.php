<?php

namespace App\Services\Publishing;

use App\Models\Post;
use App\Models\SocialAccount;
use App\Services\Meta\MetaPublishingService;
use App\Services\Snapchat\SnapchatPublishingService;
use App\Services\TikTok\TikTokPublishingService;
use App\Services\X\XPublishingService;
use RuntimeException;

class PublishingRouter
{
    public function __construct(
        private readonly MetaPublishingService $meta,
        private readonly TikTokPublishingService $tiktok,
        private readonly SnapchatPublishingService $snapchat,
        private readonly XPublishingService $x,
    ) {}

    public function publish(SocialAccount $account, Post $post): string
    {
        return match ($account->provider) {
            'instagram', 'facebook' => $this->meta->publish($account, $post),
            'tiktok'                => $this->tiktok->publish($account, $post),
            'snapchat'              => $this->snapchat->publish($account, $post),
            default                 => $this->x->publish($account, $post),
        };
    }
}
