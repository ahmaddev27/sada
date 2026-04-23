<?php

// SCH-03: queue job — publish a single post via Meta API

namespace App\Jobs;

use App\Models\Post;
use App\Models\SocialAccount;
use App\Notifications\PostFailedNotification;
use App\Notifications\PostPublishedNotification;
use App\Services\Publishing\PublishingRouter;
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

    public function handle(PublishingRouter $publisher): void
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
            $this->markFailed($post, 'No social account linked.');
            return;
        }

        try {
            $externalId = $publisher->publish($account, $post);

            $post->update([
                'status'       => 'published',
                'published_at' => now(),
                'metadata'     => array_merge((array) $post->metadata, ['external_id' => $externalId]),
            ]);

            $post->user->notify(new PostPublishedNotification($post));
        } catch (Throwable $e) {
            // On final attempt, mark failed and notify
            if ($this->attempts() >= $this->tries) {
                $this->markFailed($post, $e->getMessage());
            }

            throw $e;
        }
    }

    // Fired by Laravel when the job exhausts all retries (covers edge cases where
    // the exception is swallowed before reaching the final-attempt branch above)
    public function failed(Throwable $e): void
    {
        $post = $this->post->fresh();

        if ($post === null) {
            return;
        }

        if ($post->status !== 'failed') {
            $this->markFailed($post, $e->getMessage());
        }
    }

    private function markFailed(Post $post, string $message): void
    {
        $reason = mb_substr($message, 0, 200);

        $post->update([
            'status'   => 'failed',
            'metadata' => array_merge((array) $post->metadata, ['error' => mb_substr($message, 0, 500)]),
        ]);

        $post->user->notify(new PostFailedNotification($post, $reason));
    }
}
