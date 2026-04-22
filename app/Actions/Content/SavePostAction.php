<?php

// CG-08

namespace App\Actions\Content;

use App\Models\Post;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SavePostAction
{
    /**
     * @param array<string, mixed> $data
     */
    public function execute(Workspace $workspace, array $data): Post
    {
        $action      = (string) ($data['action'] ?? 'draft');
        $status      = $this->resolveStatus($action);
        $scheduledAt = $status === 'scheduled' && ! empty($data['scheduled_for'])
            ? Carbon::parse((string) $data['scheduled_for'])
            : null;

        return Post::create([
            'workspace_id'     => $workspace->id,
            'user_id'          => Auth::id(),
            'content'          => (string) ($data['content'] ?? ''),
            'hashtags'         => is_array($data['hashtags'] ?? null) ? $data['hashtags'] : [],
            'platform'         => (string) ($data['platform'] ?? 'instagram'),
            'content_type'     => (string) ($data['content_type'] ?? 'post'),
            'dialect'          => (string) ($data['dialect'] ?? 'fos'),
            'status'           => $status,
            'scheduled_for'    => $scheduledAt,
            'social_account_id'=> isset($data['social_account_id']) ? (int) $data['social_account_id'] : null,
            'metadata'         => [],
        ]);
    }

    private function resolveStatus(string $action): string
    {
        return match ($action) {
            'schedule' => 'scheduled',
            'publish'  => 'published',
            default    => 'draft',
        };
    }
}
