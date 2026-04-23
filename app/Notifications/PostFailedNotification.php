<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('تنبيه: فشل نشر منشورك')
            ->greeting("مرحباً {$notifiable->name}!")
            ->line('لم يتم نشر منشورك بسبب خطأ تقني.');

        if ($this->reason) {
            $mail->line("السبب: {$this->reason}");
        }

        return $mail
            ->action('مراجعة المنشورات', url('/posts'))
            ->salutation('فريق صدى');
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
