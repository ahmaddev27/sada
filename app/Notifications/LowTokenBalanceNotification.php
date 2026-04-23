<?php

// BIL-04: notify user when token balance drops below the warning threshold

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowTokenBalanceNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly int $balance) {}

    /** @return string[] */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تنبيه: رصيدك من التوكنز منخفض')
            ->greeting("مرحباً {$notifiable->name}!")
            ->line("رصيدك الحالي هو {$this->balance} توكن فقط.")
            ->line('لا تنتهِ من رصيدك — اشحن الآن واستمر في إنشاء محتوى تسويقي مميز.')
            ->action('شحن التوكنز', url('/billing'))
            ->salutation('فريق صدى');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'low_balance',
            'title'   => 'رصيدك من التوكنز منخفض',
            'body'    => "رصيدك الحالي هو {$this->balance} توكن. اشحن الآن لمواصلة التوليد.",
            'link'    => '/billing',
            'balance' => $this->balance,
        ];
    }
}
