<?php

// CON-12: Snapchat publishing — ads only (organic posting not supported by Snap API)

namespace App\Services\Snapchat;

use App\Models\Post;
use App\Models\SocialAccount;
use RuntimeException;

class SnapchatPublishingService
{
    public function publish(SocialAccount $account, Post $post): string
    {
        // Snapchat API does not support organic post/story scheduling.
        // Only paid Snap Ads are available via adsapi.snapchat.com.
        // Organic publishing routes should never reach this service.
        throw new RuntimeException(
            'Snapchat does not support organic publishing. Use the Ads API for paid campaigns.'
        );
    }
}
