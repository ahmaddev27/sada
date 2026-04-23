<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// AUTH-01: welcome notification sent after workspace creation
class WelcomeNotification extends Notification
{
    use Queueable;

    /** @return string[] */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('مرحباً بك في صدى!')
            ->greeting("أهلاً {$notifiable->name}!")
            ->line('يسعدنا انضمامك إلى صدى — منصة التسويق الرقمي بالذكاء الاصطناعي للسوق الخليجي.')
            ->line('ابدأ الآن بتوليد محتواك التسويقي بلهجتك الخليجية في ثوانٍ.')
            ->action('ابدأ التوليد', url('/generate'))
            ->salutation('فريق صدى');
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
