<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

// AUTH-01: welcome notification sent after workspace creation
class WelcomeNotification extends Notification
{
    use Queueable;

    /** @return string[] */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'welcome',
            'title' => 'مرحباً بك في صدى!',
            'body'  => 'ابدأ بتوليد محتواك التسويقي الأول بلهجتك الخليجية.',
            'link'  => '/generate',
        ];
    }
}
