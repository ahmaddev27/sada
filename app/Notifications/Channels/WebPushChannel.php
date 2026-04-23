<?php

namespace App\Notifications\Channels;

use App\Services\WebPushService;
use Illuminate\Notifications\Notification;

class WebPushChannel
{
    public function __construct(private readonly WebPushService $service) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWebPush')) {
            return;
        }

        /** @var array{title: string, body: string, url?: string} $data */
        $data = $notification->toWebPush($notifiable);

        $this->service->sendToUser(
            $notifiable,
            $data['title'],
            $data['body'],
            $data['url'] ?? '/',
        );
    }
}
