<?php

// SCH-02: artisan command — find due posts and dispatch PublishPostJob

namespace App\Console\Commands;

use App\Jobs\PublishPostJob;
use App\Models\Post;
use Illuminate\Console\Command;

class DispatchDuePosts extends Command
{
    protected $signature   = 'posts:dispatch-due';
    protected $description = 'Dispatch PublishPostJob for all scheduled posts whose time has arrived';

    public function handle(): int
    {
        $due = Post::withoutGlobalScopes()
            ->where('status', 'scheduled')
            ->where('scheduled_for', '<=', now())
            ->get();

        foreach ($due as $post) {
            PublishPostJob::dispatch($post);
            $this->line("Dispatched post #{$post->id}");
        }

        $this->info("Dispatched {$due->count()} post(s).");

        return Command::SUCCESS;
    }
}
