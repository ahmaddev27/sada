<?php

// SCH-03: queue job — publish a single post via Meta API

namespace App\Jobs;

use App\Models\Post;
use App\Models\SocialAccount;
use App\Services\Meta\MetaPublishingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class PublishPostJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    // SCH-06: retry up to 3 times with exponential backoff
    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly Post $post,
    ) {}

    public function handle(MetaPublishingService $publisher): void
    {
        // Re-fetch fresh state — post may have been edited since dispatch
        $post = $this->post->fresh();

        if ($post === null || $post->status !== 'scheduled') {
            return;
        }

        $account = $post->social_account_id
            ? SocialAccount::withoutWorkspaceScope()->find($post->social_account_id)
            : null;

        if ($account === null) {
            $post->update([
                'status'   => 'failed',
                'metadata' => array_merge((array) $post->metadata, ['error' => 'No social account linked.']),
            ]);
            return;
        }

        try {
            $externalId = $publisher->publish($account, $post);

            $post->update([
                'status'       => 'published',
                'published_at' => now(),
                'metadata'     => array_merge((array) $post->metadata, ['external_id' => $externalId]),
            ]);
        } catch (Throwable $e) {
            // On final attempt, mark failed
            if ($this->attempts() >= $this->tries) {
                $post->update([
                    'status'   => 'failed',
                    'metadata' => array_merge((array) $post->metadata, ['error' => mb_substr($e->getMessage(), 0, 500)]),
                ]);
            }

            throw $e;
        }
    }
}
