<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// SCH-07: notify user when scheduled post is published
class PostPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Post $post) {}

    /** @return string[] */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $platform = $this->platformLabel($this->post->platform);

        return (new MailMessage)
            ->subject("✓ تم نشر منشورك على {$platform}")
            ->greeting("مرحباً {$notifiable->name}!")
            ->line("تم نشر منشورك على {$platform} بنجاح.")
            ->action('عرض المنشورات', url('/posts'))
            ->salutation('فريق صدى');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'post_published',
            'title'    => 'تم نشر المنشور',
            'body'     => 'تم نشر منشورك على ' . $this->platformLabel($this->post->platform) . ' بنجاح.',
            'link'     => '/posts',
            'post_id'  => $this->post->id,
            'platform' => $this->post->platform,
        ];
    }

    private function platformLabel(string $platform): string
    {
        return match ($platform) {
            'instagram' => 'انستجرام',
            'facebook'  => 'فيسبوك',
            'tiktok'    => 'تيك توك',
            'snapchat'  => 'سناب شات',
            default     => 'تويتر',
        };
    }
}
