<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    private WebPush $webPush;

    public function __construct()
    {
        $auth = [
            'VAPID' => [
                'subject'    => config('services.vapid.subject'),
                'publicKey'  => config('services.vapid.public_key'),
                'privateKey' => config('services.vapid.private_key'),
            ],
        ];

        $this->webPush = new WebPush($auth);
    }

    public function sendToUser(User $user, string $title, string $body, string $url = '/'): void
    {
        $subscriptions = PushSubscription::where('user_id', $user->id)->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $payload = json_encode([
            'title' => $title,
            'body'  => $body,
            'url'   => $url,
            'icon'  => '/favicon.svg',
        ]);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint'        => $sub->endpoint,
                'keys'            => [
                    'p256dh' => $sub->p256dh_key,
                    'auth'   => $sub->auth_key,
                ],
            ]);

            $this->webPush->queueNotification($subscription, $payload);
        }

        foreach ($this->webPush->flush() as $report) {
            if ($report->isSubscriptionExpired()) {
                $endpoint = (string) $report->getRequest()->getUri();
                PushSubscription::where('endpoint_hash', hash('sha256', $endpoint))->delete();
            }
        }
    }
}
