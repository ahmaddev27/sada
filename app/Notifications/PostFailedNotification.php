<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

// SCH-07: notify user when scheduled post fails to publish
class PostFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Post $post,
        private readonly string $reason = '',
    ) {}

    /** @return string[] */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'post_failed',
            'title'    => 'فشل نشر المنشور',
            'body'     => 'لم يتم نشر منشورك.' . ($this->reason ? ' السبب: ' . $this->reason : ''),
            'link'     => '/posts',
            'post_id'  => $this->post->id,
            'platform' => $this->post->platform,
        ];
    }
}
